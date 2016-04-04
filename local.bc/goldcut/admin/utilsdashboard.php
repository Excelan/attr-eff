<?php

function list_adm_utils($directory)
{
	if (file_exists($directory))
	{
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);
		$objects->setMaxDepth(5);
		println("<h2>Утилиты</h2>");
		foreach ($objects as $fileinfo) 
		{
			if ($fileinfo->isFile()) 
			{
				$fname = $fileinfo->getFilename();
				$fpath = $fileinfo->getPath();
				if (substr($fname,-4,4) == '.php')
				{
					$n = substr($fname,0,-4);
					echo "<p><a href='/goldcut/admin/?localplugin=$n'>$n</a></p>";
				}
			}
		}
	}
}

$directory1 = BASE_DIR.DIRECTORY_SEPARATOR.'adminutils';
$directory2 = BASE_DIR.DIRECTORY_SEPARATOR.'goldcut/adminutils';

list_adm_utils($directory1);
list_adm_utils($directory2);
?>