<?php
/**
TODO chmod -R o+w media thumb tmp log preview
check after original copy

nginx file upload module http://www.grid.net.ru/nginx/upload.en.html

original needed?
pre cut/trim original
*/
class File extends EManager
{
	protected function config()
	{
		$this->behaviors[] = 'general_crud';
	}
	
	
	
	public function toXMLdecorator(&$dom, &$datarow)
	{
		$id = $datarow->id;
		$mediatype = $datarow->mediatype;
		//$x = $dom->firstChild->firstChild;
		$domx = new DOMXPath($dom);
		$entries = $domx->evaluate("//data");
		foreach ($entries as $n) $x = $n;
		$original = realpath(BASE_DIR.'/original/'.$id .'.'. $mediatype);
		if ($original) // file exists
		{
			$xfield = $dom->createElement("field");
			$xfield->setAttribute('name', 'base64data');
			$base64image = base64_encode_data_from_file($original);
			$text = $dom->createTextNode($base64image);
			$xfield->appendChild($text);
			$x->appendChild($xfield);
		}
	}
	
	public function create($message)
	{
		$uuid = new UUID($message->id);
		$filename = Utils::filename_extension($message->uri);
		$uri = $filename['name'];
		//$ext_lc = strtolower($filename['extension']);
		//$ext_lc = ($ext_lc) ? $ext_lc : $message->mediatype;
		$ext_lc = $message->mediatype;
		$base64data = $message->base64data;
		$filebin = base64_decode_file($base64data);
		$file =  BASE_DIR . '/original/' . $uuid . "." . $ext_lc;
		save_data_as_file($file, $filebin);
		$message->filesize = filesize($file);
		$message->mediatype = $ext_lc;
		$message->uri = $uri;
		// TRY!
		try {
			Entity::store($message);
		}
		catch (Exception $e)
		{
			unlink($file);
			throw $e;
		}
	}
	
	/**
	! ТУТ ПРЕОПРЕДЕЛЕН ВЕСЬ МЕТОД РЕСИВ А НЕ ТОЛЬКО КОНКТРЕНЫЙ АКШН
	*/
	public function delegatecreation($message) // recieve(Message $message)
	{
		//if ($message->action != 'create') throw new Exception('File can only be created. Load or any another actions are not implemented');
		$uuid = new UUID($message->id);
		
		$fileURN = null;

		// file in message not exists
		$original_file_uri = $message->file;
		if (!file_exists($original_file_uri))
			throw new Exception("Not exists file {$original_file_uri} in message");

		// ? file or uri where from?
		if (!strlen($message->uri))
			$filename = Utils::filename_extension($message->file);
		else
			$filename = Utils::filename_extension($message->uri);
			
		$uri = $filename['name'];
		$ext_lc = strtolower($filename['extension']);
		$originalFileSize = filesize($original_file_uri);
		
		$saved_original_file_uri =  BASE_DIR . '/original/' . $uuid . "." . $ext_lc;
		
		//$saved_original_file_uri = $original_file_uri; // dont copy, use original as "ogirinal"
		$srcfile = $original_file_uri;
		//$srcfile = $saved_original_file_uri;
			
		/**
		
		*/
		
		$filePolicy = new Message();
		$filePolicy->store = true;
		
		if (in_array($ext_lc, array("jpg","gif","png","bmp","jpeg","tiff","tif")))
		{
			// ->origin used for imagesize, makethumb
			if ($ext_lc == 'jpeg') $ext_lc = 'jpg';
			if ($ext_lc == 'tif') $ext_lc = 'tiff';
			
			$photoManager = new Photo();
			
			// trim modifies Original!
			$precheck = new Message();
			$precheck->action = 'precheck';
			$precheck->urn = 'urn:photo';
			$precheck->destination = $message->destination;
			$precheck->origin = $srcfile;
			$filePolicy = $photoManager->recieve($precheck);
			
			if ($filePolicy->store)
			{
				$fileURN = 'urn:file-'.$uuid;
			}
			
			$message->clear('file');
			$message->clear('create');
			$message->merge(array( 'action' => 'delegatecreation', 'urn' => 'urn:photo', 'ext'=> $ext_lc, 'file' => array("urn" => $fileURN, "origin" => $srcfile, "uri"=>$uri) ));
			
			$result = $photoManager->recieve($message);
		}
		else if (AUDIO_ENABLED === true && in_array($ext_lc, array("mp3", "aac", "ogg", "wma", "wav")))
		{
			if ($filePolicy->store)
			{
				$fileURN = 'urn:file-'.$uuid;
			}
			
			$message->clear('file');
			$message->clear('create');
			$message->merge(array( 'action' => 'create', 'urn' => 'urn:audio', 'ext'=> $ext_lc, 'file' => array("urn" => $fileURN, "origin" => $srcfile, 'uri' => $filename['name']) ));
			$attachManager = new Audio();
			$result = $attachManager->recieve($message);		
		}
		else if (VIDEO_ENABLED === true && in_array($ext_lc, array("mp4", "m4v", "mov", "avi", "3gp", "mpg", "mpeg", "flv", "wmv")))
		{
			if ($filePolicy->store)
			{
				$fileURN = 'urn:file-'.$uuid;
			}
			
			$message->clear('file');
			$message->clear('create');
			$message->merge(array( 'action' => 'create', 'urn' => 'urn:video', 'ext'=> $ext_lc, 'file' => array("urn" => $fileURN, "origin" => $srcfile, 'uri' => $filename['name']) ));
			$attachManager = new Video();
			$result = $attachManager->recieve($message);		
		}				
		else if (DOCUMENT_ENABLED === true && in_array($ext_lc, array("doc", "xls", "docx", "xlsx", "pdf", "odt", "ods")))
		{
			if ($filePolicy->store)
			{
				$fileURN = 'urn:file-'.$uuid;
			}
			
			$message->clear('file');
			$message->clear('create');
			$message->merge(array( 'action' => 'create', 'urn' => 'urn:legacydocument', 'ext'=> $ext_lc, 'file' => array("urn" => $fileURN, "origin" => $srcfile, 'uri' => $filename['name']) ));
			$attachManager = new Attach();
			$result = $attachManager->recieve($message);
		}
		/**
		TODO ELSE IF!
		*/
		else if ((ATTACH_ENABLED === true && in_array($ext_lc, array("zip", "rar", "7z", "gz", "tar"))) or ANY_AS_ATTACH === true) 
		{
			if ($filePolicy->store)
			{
				$fileURN = 'urn:file-'.$uuid;
			}
			
			$message->clear('file');
			$message->clear('create');
			$message->merge(array( 'action' => 'create', 'urn' => 'urn:attach', 'ext'=> $ext_lc, 'file' => array("urn" => $fileURN, "origin" => $srcfile, 'uri' => $filename['name']) ));
			$attachManager = new Attach();
			$result = $attachManager->recieve($message);
		}
		else
		{
			$m = new Message();
			$m->error = 409;
			$m->back = $originalFileSize;
			$m->file = $filename['name'].'.'.$ext_lc;
			$m->info = 'Unallowed file format '.$ext_lc;
			$result = $m;
		}
		
		
		// STORE ORIGINAL
		if (is_uploaded_file($original_file_uri))
			move_uploaded_file( $original_file_uri, $saved_original_file_uri);
		else
			copy($original_file_uri, $saved_original_file_uri);
		
		if ($filePolicy->store)
		{
			// create original urn-file
			$fm = new Message();
			$fm->action = 'create';
			$fm->urn = 'urn:file';
			$fm->id = $uuid->toInt();
			$fm->uri = $filename['name'];
			$fm->filesize = $originalFileSize;
			$fm->created = TimeOp::now();
			$fm->mediatype = $ext_lc;
			
			$fileCreated = Entity::store($fm);
			Broker::instance()->send($fileCreated, "ENTITY", "after.create.file");
		}
		
		return $result;
		
	}
	
	public static function extendFields(&$datarow, $E)
	{
		$oext = ($datarow['ext']) ? $datarow['ext'] : 'jpg';
		$original = realpath(BASE_DIR.'/original/'.$datarow['id'].'.'. $oext);
		$datarow['original'] = $original;
	}
}

?>