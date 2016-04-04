<table border=0 width=100%>
<tr valign=top>

<td width=40%>

	<h3>Админ</h3>
	<ul id='dashboard-actions'>
		<?php
			if (ENABLE_CONFIGS_CACHE === true && Cache::is_enabled()) echo '<li><a href="/goldcut/admin/?plugin=clearcache&key=sys:config">Сброс кеша конфигурации</a></li>';
			if (LEGACY_ENTITY_CONFIGS_ASPHPSRC === true) echo '<li><a href="/goldcut/admin/?localplugin=export.entity.configs">Re-export entity xml configs</a></li>';
		?>
<!--		<li><a href="/goldcut/sync.php?action=status">Sync STATUS</a></li>-->
<!--		<li><a href="/goldcut/sync.php?action=push">Sync PUSH to server</a></li>-->
<!--		<li><a href="/goldcut/sync.php?action=pull">Sync PULL from server</a></li>-->
		<li><a href="/goldcut/admin/db.migrate.php">Migrate DB</a></li>
		<li><a href="/goldcut/admin/db.migrate.alter.php">Alter columns DB</a></li>
		<!-- <li><a href="/goldcut/admin/?localplugin=plv8generateconfigs">PLV8 generate configs</a></li> -->

		<li><a href="/goldcut/admin/?localplugin=gates.list">Gates list</a></li>
		<li><a href="/goldcut/admin/?localplugin=formslist">Forms list</a></li>

		<li><a href="/goldcut/admin/entitymenu.php?domain=Document">DMS Documents</a></li>
		<li><a href="/goldcut/admin/entitymenu.php?notdomain=Document&notsystem=yes">DMS Non Document &amp; non system</a></li>
		<li><a href="/goldcut/test/sys/elist.php">Entity structure plain</a></li>
		<li><a href="/db">Entity structure UML</a></li>
		<li><a href="/goldcut/admin/?localplugin=export.data.xml">Export data to XML</a></li>
		<li><a href="/goldcut/admin/?localplugin=import.xml">Import data from XML</a></li>

		<li><a href="/goldcut/admin/?plugin=dms.clear">DMS Clear Documents &amp; Inboxes</a></li>
<!--		<li><a href="/goldcut/test/sys/production.load.php">Пересоздание базы данных и загрузка тестовых XML данных</a></li>-->

		<?php
		if (ENABLE_LISTDB === true) {
			?>
			<li><a href="/goldcut/admin/?plugin=rebuildlistdb">Sync list db with rdbms</a></li>
			<?php
		}
		?>



		<li><a href="/goldcut/admin/?plugin=signal.schedule&schedule=hourly">Schedule hourly signal</a></li>
		<li><a href="/goldcut/admin/?plugin=signal.schedule&schedule=daily">Schedule daily signal</a></li>
		<li><a href="/goldcut/admin/?plugin=signal.schedule&schedule=monthly">Schedule monthly signal</a></li>
		<li><a href="/goldcut/admin/?plugin=timedebug">Time client/server debug</a></li>

		<li><a href="/goldcut/admin/?localplugin=git.stat">Git stat</a></li>
		<li><a href="/goldcut/admin/?localplugin=git.pull.all">Git commit, pull, push with goldcut, lib</a></li>
		<li><a href="/goldcut/admin/?plugin=cleanendfilenewlines">Clean all .php files from end file newlines</a></li>
	</ul>

</td>
<td>

<?php

	echo "<p>ENV: ".ENV."</p>";

	if (ENV == LOG_ENV) echo "<p>LOG ENABLED</p>";

	if (GATES_ENABLED === true) echo "<p>GATES ENABLED</p>";
	if (EXTERNAL_GATES_ENABLED === true) echo "<p>EXTERNAL GATES ENABLED</p>";

	if (PHP_INT_MAX > 2147483647) $BIT = 64;
	else $BIT = 32;

	if (FORCE32BIT === true) echo "<p>FORCE 32 bit ENTITY ID ON {$BIT}bit PLATFORM</p>";
	else
		echo "<p>NO FORCE32BIT ".PHP_INT_MAX."</p>";

	if (FORCEBIGINTS === true) echo "<p>FORCE BIG INTS</p>";

	if (INICONFIGS === true) echo "<p>INI BASE CONFIGS</p>";
	else
		echo "<p style='color: red'>! LEGACY PHP BASE CONFIGS</p>";
	if (LEGACY_ENTITY_CONFIGS_ASPHPSRC === true) echo "<p style='color: red'>! LEGACY ENTITY CONFIG FORMAT</p>";
	if (LEGACY_CONFIG_FIELDS_ASPHP === true) echo "<p style='color: red'>! LEGACY FIELD CONFIG FORMAT</p>";
	if (NEWUSERMODEL !== true) echo "<p style='color: red'>! LEGACY USERMODEL</p>";

    if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) // pgsql
        echo "<p>POSTGRESQL DB {$GLOBALS['CONFIG'][ENV]['DB']['DBNAME']}</p>";
	else
		echo "<p>MYSQL</p>";
	if (ENABLE_LISTDB === true)
		echo "<p>REDIS AS LISTDB</p>";

	if (EXTENDEDSTRUCTURE === true) echo "<p>EXTENDED STRUCTURE</p>";

	if (SENDMAILINENVDEV === true) echo "<p>SEND MAIL IN ENV DEV</p>";
	elseif (ENV == 'DEVELOPMENT' && SENDMAILINENVDEV !== true) echo "<p style='color: red'>! NO SEND MAIL IN ENV DEV</p>";
	if (defined('POSTMARKAPI')) echo "<p>POSTMARKAPI ENABLED</p>";

	echo "<p>CACHE BACKEND: ".Cache::backend() . '</p>';

	if (Cache::is_enabled())
	{
		$key = 'gctest';
		if ($res = Cache::get($key))
		{
			printlnd("$res - from cache");
		}
		else
		{
			$res = mt_rand(1,1000);
			$cachedOk = Cache::put($key, $res);
			printlnd("Cache this: $res. Cache backend returned status: $cachedOk");
		}
	}

	// git diff --numstat "@{1 day ago}"
	// git log --stat --summary

	if (GITUSED === true && (ENV == 'DEVELOPMENT' || $_GET['git'])) {
		$gitBranch = shell_run_command(BASE_DIR, 'git rev-parse --abbrev-ref HEAD');
		$ge = explode("\n", $gitBranch);
		if ($ge[0] == 'master')
			println('site git branch ' . $ge[0], 1, TERM_GRAY);
		else {
			if ($_GET['gitmaster']) $gitBranch = shell_run_command(BASE_DIR, 'git checkout master');
			println('!!! site git branch ' . $ge[0], 1, TERM_RED);
		}
		$gitBranch = shell_run_command(BASE_DIR, 'git log --pretty=format:"%h : %an : %ar : %s" --no-merges | head -15');
		$ge = explode("\n", $gitBranch);
		array_pop($ge);
		foreach ($ge as $commit) println($commit,2,TERM_GRAY);

		$gitBranch = shell_run_command(BASE_DIR . '/goldcut', 'git rev-parse --abbrev-ref HEAD');
		$ge = explode("\n", $gitBranch);
		if ($ge[0] == 'master')
			println('goldcut git branch ' . $ge[0], 1, TERM_GRAY);
		else {
			if ($_GET['gitmaster']) $gitBranch = shell_run_command(BASE_DIR . '/goldcut', 'git checkout master');
			println('!!! goldcut git branch ' . $ge[0], 1, TERM_RED);
		}
		$gitBranch = shell_run_command(BASE_DIR.'/goldcut', 'git log --pretty=format:"%h : %an : %ar : %s" --no-merges | head -3');
		$ge = explode("\n", $gitBranch);
		array_pop($ge);
		foreach ($ge as $commit) println($commit,2,TERM_GRAY);

		$gitBranch = shell_run_command(BASE_DIR . '/lib', 'git rev-parse --abbrev-ref HEAD');
		$ge = explode("\n", $gitBranch);
		if ($ge[0] == 'master')
			println('lib git branch ' . $ge[0], 1, TERM_GRAY);
		else {
			if ($_GET['gitmaster']) $gitBranch = shell_run_command(BASE_DIR . '/lib', 'git checkout master');
			println('!!! lib git branch ' . $ge[0], 1, TERM_RED);
		}
	}

		$directory = BASE_DIR.DIRECTORY_SEPARATOR.'test';
		if (file_exists($directory))
		{
			$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);
			$objects->setMaxDepth(5);
			println("<br><h2>tests</h2>");
			foreach ($objects as $fileinfo)
			{
				if ($fileinfo->isFile())
				{
					$fname = $fileinfo->getFilename();
					$fpath = $fileinfo->getPath();
					$lpath = substr($fpath,strlen(BASE_DIR));
					if (substr($fname,-4,4) == '.php')
					{
						echo "<p><a href='{$lpath}/{$fname}'>$fname</a></p>";
					}
				}
			}
		}

		if ($_GET['systemtests'])
		{
		$directory = BASE_DIR.DIRECTORY_SEPARATOR.'/goldcut/test';
		if (file_exists($directory))
		{
			$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);
			$objects->setMaxDepth(5);
			println("<h2>Системные тесты</h2>");
			foreach ($objects as $fileinfo)
			{
				if ($fileinfo->isFile())
				{
					$fname = $fileinfo->getFilename();
					$fpath = $fileinfo->getPath();
					$lpath = substr($fpath,strlen(BASE_DIR)+1);
					if (substr($fname,-4,4) == '.php' || substr($fname,-5,5) == '.html')
					{
						echo "<p><a href='{$lpath}/{$fname}'>$fname</a></p>";
					}
				}
			}
		}
		}


	if ($_GET['adminutils']) include "utilsdashboard.php";

?>

</td>
</tr>
</table>
