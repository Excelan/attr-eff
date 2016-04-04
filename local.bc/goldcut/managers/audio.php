<?php

class Audio extends EManager
{
	public function create($message)
	{
		//printlnd('AUDIO',1,TERM_GREEN);
		//printlnd($message,2,TERM_GREEN);
		$origin = $message->file->origin;
		$file_urn = new URN($message->file->urn);
		$filesize = filesize($origin);
		$uri = $message->file->uri;
		$message->clear('file');
		$message->merge( array('file'=>$file_urn, 'uri' => $uri, 'ext'=>$message->ext, 'id'=>$file_urn->uuid()->toInt(), 'filesize'=>$filesize) );

		$audiofile = $origin;
		
		$outfile1 = BASE_DIR . '/audio/' . $file_urn->uuid() . ".mp3";
		$outfile2 = BASE_DIR . '/audio/' . $file_urn->uuid() . ".ogg";
		
		if (MEDIA_TRANSCODING === true)
		{
			ob_start();
			passthru("mp3info -p \"%S\" \"{$audiofile}\" 2>&1");
			$duration = ob_get_contents();
			ob_end_clean();
			$duration_in_sec = (integer) $duration;
			$message->duration = $duration_in_sec;
			
			//$cutfrom = "-ss 00:0:00";
			//$cuttime = "-t 0:0:10";
			
			$command1 = "ffmpeg -f mp3 -acodec mp3 -ab 256k {$cutfrom} {$cuttime} -y -i \"{$audiofile}\" {$outfile1} 2>&1";
			$command2 = "ffmpeg -i {$audiofile} -f ogg -acodec libvorbis -aq 5 {$cutfrom} {$cuttime} -y {$outfile2} 2>&1"; // libvorbis
			
			ob_start();
			passthru($command1);
			passthru($command2);
			$tc_report = ob_get_contents();
			ob_end_clean();
		}
		else
		{
			copy($audiofile, $outfile);
		}

		$attach = Entity::store($message);
		$r = new Message();
		$r->urn = $attach->urn;
		$r->uri = $uri;
		$r->duration = $duration_in_sec;
		return $r;
	}

}
?>