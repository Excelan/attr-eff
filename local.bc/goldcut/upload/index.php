<?php
require dirname(__FILE__).'/../boot.php';

$us = new Message('{"urn": "urn-user", "action": "session"}');
$sess = $us->deliver();
if ($sess->warning)
{
	//header("HTTP/1.0 503 Server Error");
	//print $sess->warning;
	//exit();
	Log::info($sess->warning, 'upload');
}
else
{
	Log::info($sess->user, 'upload');
}

/**
TODO Add ACL - allow upload to registered & admin. Anon only if configured + limits

$root_login = 'root';
$root_password = ROOT_PASS;
// FLASH has access to cookies
if ($_COOKIE['login']) 
{
	if (md5($root_login.$root_password) == $_COOKIE['login']) 
		$username = 'root';
	else
		die("You have sent a bad cookie.");
}
else
{
	header("HTTP/1.0 403 Forbidden");
	exit(1);
}
*/
function file_upload_error_message($error_code) 
{
	switch ($error_code) 
	{
		case UPLOAD_ERR_INI_SIZE:
			return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
		case UPLOAD_ERR_FORM_SIZE:
			return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
		case UPLOAD_ERR_PARTIAL:
			return 'The uploaded file was only partially uploaded';
		case UPLOAD_ERR_NO_FILE:
			return 'No file was uploaded';
		case UPLOAD_ERR_NO_TMP_DIR:
			return 'Missing a temporary folder';
		case UPLOAD_ERR_CANT_WRITE:
			return 'Failed to write file to disk';
		case UPLOAD_ERR_EXTENSION:
			return 'File upload stopped by extension';
		default:
			return 'Unknown upload error';
	}
}

if ($_FILES['Filedata']['error'] === UPLOAD_ERR_OK)
{
	// "UPLOADED TO TMP";
}
else
{
	echo file_upload_error_message($_FILES['Filedata']['error']);
}

if($_FILES['Filedata']['name'])
{
	if($_FILES['Filedata']['size'] != 0)
	{
		if(is_uploaded_file($_FILES['Filedata']['tmp_name']))  // Проверяем загрузился ли файл на сервер
		{
			Log::debug(json_encode($_COOKIE), 'upload');
			Log::debug(json_encode($_POST), 'upload');
			
			$m = new Message($_POST);
			$m->action = 'create';
			$m->urn = $_POST['destination'];
			if ($sess->user) $m->user = $sess->user;
			$m->file = $_FILES['Filedata']['tmp_name'];
			$m->uri = basename($_FILES['Filedata']['name']);
			$r = $m->deliver();
			print($r);
		}
		else
		{
			echo 'Прозошла ошибка при загрузке файла на сервер';
		}
	}
	else 
	{ 
		echo 'Размер файла должен быть больше 0';
	}
}
else
{
	echo 'Файл должен иметь название';
}

?>