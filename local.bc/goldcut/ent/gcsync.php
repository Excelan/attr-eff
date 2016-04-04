<?php 
class GCSync
{
	
	// max updated time of file, not time of sync
	public static function repoLastSync($reponame)
	{
		$file = BASE_DIR."/tmp/sync/lastsync-{$reponame}.txt";
		if (file_exists($file))
		{
			$lastsync = (int) file_get_contents($file);
			$lastsync_tm = $lastsync;
			$lastsync = time() - $lastsync;
			if ($lastsync > 60)
			{
				$tm = new DateTime();
				$tm->setTimestamp($lastsync_tm);
				println("$reponame last synced ".Date_Difference::getString($tm),1,TERM_GRAY);
			}
			else
			{
				println("$reponame last synced {$lastsync} seconds ago",1,TERM_GRAY);	
			}
			return (int) $lastsync;
		}
		else
		{
			println("$reponame no last sync",1,TERM_YELLOW);
			return null;
		}
	}
	
	public static function repoLastSyncUpdate($reponame, $changed_files)
	{
		if (count($changed_files))
		{
			$newestFileTime = self::newestFile($changed_files);
			$of = fopen(BASE_DIR."/tmp/sync/lastsync-{$reponame}.txt", "w");
			fwrite($of, $newestFileTime);
			fclose($of);
		}
	}
	
	public static function reportChanged(array $changed_files)
	{
		if (!count($changed_files))
		{
			println("Unchanged {$reponame}",1,TERM_VIOLET); 
			// continue; 
		}
		if (count($changed_files) < 50)
		{
			foreach ($changed_files as $cf)
			{
				$tm = new DateTime();
				$tm->setTimestamp($cf['created']);
				$tsdiff = Date_Difference::getString($tm);
				println("$tsdiff {$cf['path']}/{$cf['name']}",1,TERM_GRAY);
			}
		}
		else
		{
			println(count($changed_files)." files changed",1,TERM_GRAY);
		}
	}
	
	public static function changed_files($folders, $after) 
	{
		$all_changed_files = array();
		foreach ($folders as $folder)
		{	
			$fullPath = BASE_DIR.'/'.$folder;
			if (!file_exists($fullPath)) continue;
			$changed_files = array();
			$files = array();
			$flags = FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
			// FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO 
			$ite = new RecursiveDirectoryIterator(BASE_DIR.'/'.$folder, $flags); // TODO exclude dot files
			foreach (new RecursiveIteratorIterator($ite) as $filename => $f) 
			{
				//if (!$f->isDot()) // +ALL EXCLUDES
				{
					$filePath = substr($f->getPath(), strlen(BASE_DIR));
					$fileName = $f->getFilename();
					$fileSize = $f->getSize();
					$fileTimeM = $f->getMTime();
					$fileTimeC = $f->getCTime();					
					$files[] = array('path' => $filePath, 'name' => $fileName, 'size' => $fileSize, 'modified' => $fileTimeM, 'created' => $fileTimeC, 'hash' => null, 'offset' => null);
				}
			}
			//print_r($files);		
			$is = function($v) use (&$after) 
			{
				return( (time() - $v['modified']) < $after);
			};
			$changed_files = array_filter($files, $is);
			$all_changed_files = array_merge($changed_files, $all_changed_files);
		}
		return $all_changed_files;
	}
	
	
	public static function newestFile($changed_files)
	{
		// NEWSEST FILE
		if (!count($changed_files)) return 0;
		foreach ($changed_files as $file)
		{
			$mtimes[] = $file['modified'];
		}
		$newestFileTime = max($mtimes);
		//print "newestFileTime {$newestFileTime}\n";
		return $newestFileTime;
	}
	
	public static function readFileSized($filename, $filesize)
	{
		$handle = fopen($filename, "r");
		$data = fread($handle, $filesize);
		fclose($handle);
		return $data;
	}
	
	public static function processData($data, $repo)
	{
		if ($repo['policy']['compress'] == 'gzip')
		{
			$data = gzcompress($data, 9);
		}
		// TODO encode
		return $data;
	}
	
	public static function deProcessData($data, $repo)
	{
		if ($repo['policy']['compress'] == 'gzip')
		{
			$data = gzuncompress($data);
		}
		// TODO decode
		return $data;
	}
	
	public static function writeAppendOpened($fp, $data)
	{
		fwrite($fp, $data);
	}
	
	public static function packFiles($changed_files, $BASE, $repo)
	{
		// PACK FILES, METADATA
		$newestFileTime = self::newestFile($changed_files);
		$COMMITS_PATH = BASE_DIR.'/tmp/sync/';
		$datafile = $COMMITS_PATH . "{$repo['name']}-{$newestFileTime}.gvcommit";
		$fp = fopen($datafile, 'w');
		$offset = 0;
		// bytes
		$bits = 32;
		$offset = 1 + 4 * $bits/8;
		$bytes14 = pack('C', $bits);
		$bytes14 .= pack('N', $offset); // head offset
		
		$head = json_encode(array('compress'=>'gzip'));
		$offset += strlen($head);
		
		$bytes14 .= pack('N', 0); // commands (reserved) offset
		$bytes14 .= pack('N', $offset); // data offset
		$bytes14 .= pack('N', 0); // metadata offset
		fwrite($fp, $bytes14);
		// head block
		fwrite($fp, $head);
		// commands block
		// ...
		// data block
		foreach ($changed_files as &$f)
		{
			$file = $BASE."{$f['path']}/{$f['name']}";
			$data = self::readFileSized($file, $f['size']);
			$processedData = self::processData($data, $repo); // process data - compress, encode
			$processedDataSize = strlen($processedData);
			// write file to datablock
			self::writeAppendOpened($fp, $processedData);
			$f['offset'] = $offset + $prevsize;
			$f['sizep'] = $processedDataSize;
			$f['hash'] = hash('sha256', $data);
			$offset = $offset + $prevsize;
			$prevsize = $processedDataSize;
		}
		$offset = $offset + $prevsize;
		// write metadata block
		$meta = json_encode($changed_files);
		$meta = self::processData($meta, $repo);
		$metaDataOffset = $offset;
		self::writeAppendOpened($fp, $meta);
		fseek($fp, (1 + 3 * $bits/8));
		fwrite($fp, pack('N', $metaDataOffset)); // metadata offset
		fclose($fp);
		return $datafile;
	}
	
	// TODO Handle broken partial unpack. Lock file. Change current version only after full success unpack.
	public static function unpackFiles2($datafile, $repo, $outdir)
	{
		$handle = fopen($datafile, "r");
		$totalSize = filesize($datafile);
		$data = fread($handle, 1);
		$i = unpack("C", $data);
		$bits = $i[1];
		$bin = fread($handle, $bits/8*4);
		$i = unpack("N*", $bin);
		list(,$headOffset, $commandsOffset, $dataOffset, $metaOffset) = $i;
		// unpack meta
		fseek($handle, $metaOffset);
		$metaSize = $totalSize-$metaOffset;
		$meta = fread($handle, $metaSize);
		$meta = json_decode(self::deProcessData($meta, $repo), true);
		
		self::unpackBlockData($handle, $meta, $repo, $outdir);
		
		fclose($handle);
	}
	
	public static function unpackBlockData($handle, $meta, $repo, $outdir)
	{
		foreach ($meta as &$f)
		{
			$filesize = $f['sizep'];
			fseek($handle, $f['offset']);
			$data = fread($handle, $filesize);
			$data = self::deProcessData($data, $repo);
			Log::info($f['name'], 'sync-server');
			$mp = $outdir.$f['path'];
			if (!file_exists($mp)) mkdir($mp, octdec(FS_MODE_DIR), true);  // TODO CONF UMASK! FS_MODE_DIR octdec(decoct(FS_MODE_DIR))
			$file = $outdir."{$f['path']}/{$f['name']}";
			$of = fopen($file, "w");
			fwrite($of, $data);
			fclose($of);
			chmod($file, octdec(FS_MODE_FILE)); // TODO CONF UMASK! decoct(octdec(FS_MODE_DIR))) octdec(decoct(FS_MODE_FILE))
		}
	}
	
	public static function consoleFileTime()
	{
		$tm = new DateTime();
		$tm->setTimestamp($f[3]);
		print $f[0]."/".$f[1]." ".Date_Difference::getString($tm)."\n";
	}
	
	/**
	save all files in repo per version to compare what changed
	return 
	*/
	public static function repositoryFilesTable($repo)
	{
		return $filesStruct;
	}
	
	public static function changedByFilesTable($filesStruct, $filesStruct)
	{
		// deleted
		// moved
		// changed
		return $changed;
	}

	public static function postUpload($url, $post)
	{
		/**
		Instead of using CURLOPT_PUT = TRUE use CURLOPT_CUSTOMREQUEST = 'PUT' and CURLOPT_CUSTOMREQUEST = 'DELETE' then just set values with CURLOPT_POSTFIELDS
		Generally speaking, most RESTful services that don't allow PUT & DELETE directly will support at least one of those strategies. You can use cURL to set a custom header if you need via the CURLOPT_HTTPHEADER option.
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT') );
		*/
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
		$response = curl_exec($ch);
		if (!$response)
		{
			if (ENV == 'DEVELOPMENT') println('curl exec error for URL '.$url.'. ERROR: '.curl_error($curl).'('.curl_errno($curl).')',1,TERM_RED);
		}
		return $response;
	}
	
	public static function postDownload($url, $post, $localPath)
	{
		$fp = fopen($localPath, 'w');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // !
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$response = curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		if (!$response)
		{
			$error = curl_error($ch).'('.curl_errno($ch).')';
			echo $error;
			return false;
		}
		else
			return true;
	}
	
	public static function sendAsDownloadStreamAndExit($datafile, $uri, $remove=false)
	{
		$content_length = filesize($datafile);
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$uri);
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $content_length);
		ob_clean();
		flush();
		readfile($datafile);
		if ($remove) unlink($datafile);
		exit;
	}
}
?>