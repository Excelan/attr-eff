<?php 
require "boot.php";

set_time_limit(0); 

// TODO remote remove removed local files
// multiuser as edit intents. 

$repos = $GLOBALS['CONFIG']['DEVELOPMENT']['REPOS'];

if (!is_array($repos)) throw new Exception('Need config option $GLOBALS[\'CONFIG\'][\'DEVELOPMENT\'][\'REPOS\']');

// cli only client run
if ($argv[1] == 'push' || $_GET['action'] == 'push' || $_POST['action'] == 'push') $push = true;
if ($argv[1] == 'pull' || $_GET['action'] == 'pull' || $_POST['action'] == 'pull') $pull = true;
if ($argv[1] == 'status' || $_GET['action'] == 'status' || $_POST['action'] == 'status') $status = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST')
	$iam = 'server';
else
	$iam = 'client';

if (!$pull and !$push and !$status) 
{
	print "sync push/pull/status\n";
	exit(1);
}

$syncWorkDir = BASE_DIR.'/tmp/sync';
if (!is_dir($syncWorkDir)) mkdir($syncWorkDir, octdec(FS_MODE_DIR), true);

if ($status)
{
	if ($iam == 'client')
	{
		foreach ($repos as $reponame => $repo)
		{
			$lastsync = GCSync::repoLastSync($repo['name']);
			$minafter = 3600*24*10;
			$after = ($lastsync) ? $lastsync : $minafter; // debug - if no lastsync, sync files changed in 10 days
			$changed_files = GCSync::changed_files($repo['folders'], $after);
			GCSync::reportChanged($changed_files);
		}
	}
}
else if ($push)
{
	if ($iam == 'client')
	{
		println("CLIENT PUSH"); // TODO compare with server file hashes and dont allow force rewrite
		foreach ($repos as $reponame => $repo)
		{
			if (!in_array('push', $repo['policy']['clientcan'])) continue;
			printH($reponame);
			$lastsync = GCSync::repoLastSync($repo['name']);
			//$minafter = 3600*24*10;
			$minafter = 3600*24*30*12;
			$after = ($lastsync) ? $lastsync : $minafter; // debug - if no lastsync, sync files changed in 10 days
			$changed_files = GCSync::changed_files($repo['folders'], $after);
			GCSync::reportChanged($changed_files);
			$BASE = BASE_DIR;
			$packedFiles = GCSync::packFiles($changed_files, $BASE, $repo); // TODO Make single header+meta+data file
			$datafile = $packedFiles;
			$datafileSize = filesize($datafile);
			// UPLOAD COMMIT TO SERVER
			$post = array( "datablock" => "@".$datafile, "repository" => $repo['name'], "key" => $repo['key'], "action" => "push" ); // "metadata" => $meta, 
			$res = GCSync::postUpload($repo['host']."goldcut/sync.php", $post);
			unlink($datafile);
			try {
				$serverResponce = json_decode($res, true);
			}
			catch (Exception $e) {
				println($e,1,TERM_RED); 
			}
			if ($serverResponce['commit'] == 1)
			{
				if ($datafileSize > 46)
					println("Synced {$reponame} with {$repo['host']}, uploaded " . Utils::formatBytes($datafileSize,2), 1, TERM_GREEN);
			}
			else
			{
				println("Sync {$reponame} with {$repo['host']} FAILED", 1, TERM_RED);
				println($res,1,TERM_RED); // error on server
			}
			//if (0) // debug - dont update synced time file
			GCSync::repoLastSyncUpdate($repo['name'], $changed_files);	
			//echo Utils::formatBytes(memory_get_peak_usage(),2);
		}
	}
	if ($iam == 'server')
	{
		Log::info('PUSHED 1', 'sync-server');//
		// TODO lock repository for write
		// CLIENT KEY: $_POST['key']
		// check repo, check server policy
		$reponame = $_POST['repository'];
		Log::info($reponame, 'sync-server');
		$repo = $repos[$reponame]; // CONF
		if (!$repo) Log::error('Dont push to unconfigured repo '.$reponame, 'sync-server');
		// check user key
		// check user policy - is this user can push?
		//$outdir = BASE_DIR.'/out';
		$outdir = BASE_DIR;
		$commitfile = $_FILES["datablock"]["tmp_name"];
		if (!$outdir) throw new Exception('no outdir');
		GCSync::unpackFiles2($commitfile, $repo, $outdir);
		// TODO increment commit $version
		$version++;
		echo json_encode(array('commit'=>$version));
		//echo Utils::formatBytes(memory_get_peak_usage(),2);
		Log::info('OK pushed '.$reponame, 'sync-server');////
	}
}
else if ($pull)
{
	if ($iam == 'server')
	{
		Log::info('PULLED', 'sync-server');
		$reponame = $_POST['repository'];
		$repo = $repos[$reponame]; // CONF	
		$changed_files = GCSync::changed_files($repos[$reponame]['folders'], (int) $_POST['lastsync']);
		$BASE = BASE_DIR;
		$packedFile = GCSync::packFiles($changed_files, $BASE, $repo);
		$datafile = $packedFile;
		$uri = 'package.'.$reponame;
		GCSync::sendAsDownloadStreamAndExit($datafile, $uri, true); // 3true - delete file after send
		// exited here!
	}	
	if ($iam == 'client')
	{
		foreach ($repos as $repo)
		{
			if (!in_array('pull', $repo['policy']['clientcan'])) continue;
			$lastsync = GCSync::repoLastSync($repo['name']);
			$after = ($lastsync) ? $lastsync : 3600*24*10; // !!! TODO if no last sync, get from server OR send own max changed ts
			$localPathDownload = BASE_DIR.'/tmp/sync/package.'.$repo['name'];
			$post = array("key" => $repo['key'], "action" => "pull", "lastsync" => $after, "repository" => $repo['name']);
			GCSync::postDownload($repo['host']."goldcut/sync.php", $post, $localPathDownload);
			$outdir = BASE_DIR;
			GCSync::unpackFiles2($localPathDownload, $repo, $outdir);
			// if (0)
			{
				$changed_files = GCSync::changed_files($repo['folders'], $after);
				// println($changed_files);
				// printlnd($after);
				GCSync::repoLastSyncUpdate($repo['name'], $changed_files); // on debug dont update to re download forever
				GCSync::reportChanged($changed_files);
			}
			unlink($localPathDownload);
			println("client pulled");
		}
	}
	
}

?>