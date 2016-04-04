<?php

class Video extends EManager
{
	public function create($message)
	{
		if (MEDIA_TRANSCODING !== true) return false;

		if (TEST_VIDEO_MODE_5SEC === true)
		{
			$cutfrom = "-ss 00:0:00";
			$cuttime = "-t 0:0:5";
		}
		else
		{
			$cutfrom = "-ss 00:0:00";
			$maxtime = '0:1:0';
			if (defined('MAXVIDEOTIME')) $maxtime = MAXVIDEOTIME;
			$cuttime = "-t ".MAXVIDEOTIME; // MAX VIDEO DULATION IN PRODUCTION
		}

		if (OS::getOS() == 'OSX') $ffmpegpath = '/opt/local/bin/';

		$origin = $message->file->origin;
		$file_urn = new URN($message->file->urn);
		$uuid_str = $file_urn->uuid();
		$filesize = filesize($origin);
		$uri = $message->file->uri;
		$message->clear('file');
		$message->merge( array('file'=>$file_urn, 'uri' => $uri, 'ext'=>$message->ext, 'id'=>$file_urn->uuid()->toInt(), 'filesize'=>$filesize) );
		$videofile = $origin;

		// GET VIDEO DURATION, ASPECT, META
		ob_start();
		passthru("ffmpeg -i \"{$videofile}\" 2>&1");
		$duration = ob_get_contents();
		ob_end_clean();
		$output = $duration;
		$search='/Duration: (.*?),/';
		$duration=preg_match($search, $duration, $matches, PREG_OFFSET_CAPTURE, 3);
		$duration = $matches[1][0];
		$timearray = explode(":", $duration);
		$min = 60*$timearray[1];
		$sec = $timearray[2];
		$ttime = $min+$sec;
		if($dot = strpos($ttime, ".")) $ttime = substr($ttime, 0, 3);
		$duration_in_sec = (integer) $ttime;
		$message->duration = $duration_in_sec;
		Log::debug("Video duration: {$message->duration}", 'video');
		// $result = ereg('framerate(.*)', $output, $regs);
		// if (isset ($regs)) var_dump($regs);
		$result = preg_match ( '[0-9]?[0-9][0-9][0-9]x[0-9][0-9][0-9][0-9]?', $output, $regs );
		if (isset ( $regs [0] ))
		{
			$wh = explode('x', $regs[0]);
			$vw = $wh[0];
			$vh = $wh[1];
			$message->width = $vw;
			$message->height = $vh;
			if (($vw/$vh > 4/3)) $message->isWide = true;
			if (!defined('VIDEO_SIZE_STD') and !defined('VIDEO_SIZE_WIDE')) throw new Exception("Options VIDEO_SIZE_WIDE and VIDEO_SIZE_STD not defined");
			if ($message->isWide)
			{
				$aspect = "-aspect 16:9";
				$resizevideo = "-s ".VIDEO_SIZE_WIDE;
				$size = VIDEO_SIZE_WIDE;
			}
			else
			{
				$aspect = "-aspect 4:3";
				$resizevideo = "-s ".VIDEO_SIZE_STD;
				$size = VIDEO_SIZE_STD;
			}
		}
		else
		{
			// DEFAULT (ERROR IN GETTING VIDEO INFO) // 640x360, 568x320
			$aspect = "-aspect 16:9";
			$size = VIDEO_SIZE_WIDE;
		}

		$sizea = explode('x',$size);
		// размеры должны быть кратны 2
		if ($sizea[0] % 2) $sizea[0] = $sizea[0]-1;
		if ($sizea[1] % 2) $sizea[1] = $sizea[1]-1;

		$resizevideo = "-s ".join('x',$sizea); // SIZE

		$outposters = "/video/{$uuid_str}.jpg";
		$outposters_file = BASE_DIR . $outposters;

		// BUILD POSTER
		ob_start();
		$postersize = $resizevideo;
		$poster_sec_start = floor($duration_in_sec / 2);
		$cmd = $ffmpegpath."ffmpeg -i \"{$videofile}\" -ss {$poster_sec_start} -vframes 1 {$postersize} -f image2 \"$outposters_file\" 2>&1";  // -r 10
		passthru($cmd);
		Log::debug("Poster cmd: {$cmd}", 'video');
		$tc_report = ob_get_contents();
		ob_end_clean();


		$webm = array('container'=>'webm','videocodec'=>'libvpx','videopresetsys'=>null,'audiocodec'=>'libvorbis','videobitratek'=>256,'audiobitratek'=>128,'framerate'=>25,'audiohz'=>44100,'audiochannels'=>2);
		$mp4 = array('container'=>'mp4','videocodec'=>'libx264','videopresetsys'=>null,'audiocodec'=>'libfaac','videobitratek'=>256,'audiobitratek'=>128,'framerate'=>25,'audiohz'=>44100,'audiochannels'=>2);
		$ogg = array('container'=>'ogg','videocodec'=>'libtheora','videopresetsys'=>null,'audiocodec'=>'libvorbis','videobitratek'=>256,'audiobitratek'=>128,'framerate'=>25,'audiohz'=>44100,'audiochannels'=>2);

		// TODO conf it
		$targetFormats = array($webm, $mp4, $ogg);

		foreach ($targetFormats as $format)
		{
			$ext = $format['container'];
			$outfile = BASE_DIR . '/video/' . $file_urn->uuid() . "." . $ext;
			$outvideo = '/video/' . $file_urn->uuid() . "." . $ext;
			$acodec = '-acodec '.$format['audiocodec'];
			$vcodec = '-vcodec '.$format['videocodec'];
			$container = '-f '.$format['container'];
			$vb = '-b '.$format['videobitratek'].'k';
			$fr = '-r '.$format['framerate'];
			$audioparam = "-ab {$format['audiobitratek']}k -ar {$format['audiohz']} -ac {$format['audiochannels']}";
			$cmd = $ffmpegpath."ffmpeg -i \"{$videofile}\" -y {$container} {$cutfrom} {$cuttime} {$acodec} {$metadata} {$audioparam} {$vcodec} {$vpre} {$vb} ${aspect} {$resizevideo} {$fr} -copyts -threads 0 \"${outfile}\" 2>&1";
			Log::debug("Transcode cmd: {$cmd}", 'video');
			ob_start();
			passthru($cmd);
			$tc_report = ob_get_contents();
			ob_end_clean();
			if (!file_exists($outfile))
			{
				Log::error("Video is not converted (out file not exists, may be /video folder not exists)", 'video');
				Log::debug($tc_report, 'video');
				throw new Exception('Video cannot be converted');
			}
		}

		$savedvideo = Entity::store($message);
		Log::debug("Saved video: {$savedvideo}", 'video');

		if (MEDIA_TRANSCODING === true)
			$icon = $outposters;
		else
			$icon = "/icon/video.png";
		$savedvideo->video = $outvideo;
		$savedvideo->thumbnail = array("uri" => $icon);
		return $savedvideo;
	}


	public static function extendFields(&$datarow, $E)
	{
		$uuid = $datarow['id'];
		$outvideo = '/video/'.$uuid;
		$outposters = "/video/{$uuid}.jpg";
		$path = BASE_DIR.$outposters;
		$refsize = getimagesize($path); // video dimensions
		$srcs .= "<source src='{$outvideo}.webm' type='video/webm'>";
		$srcs .= "<source src='{$outvideo}.mp4' type='video/mp4'>";
		$srcs .= "<source src='{$outvideo}.ogg' type='video/ogg'>";
		$datarow['video'] = new Message(array("uri" => $outvideo, 'player' => "<video preload=\"none\" width=\"{$refsize[0]}\" height=\"{$refsize[1]}\" poster=\"{$outposters}\" controls='controls'>\n{$srcs}\n</video>"));
		$datarow['poster'] = new Message(array("uri" => $outposters, 'img' => "<img src='{$outposters}' width=\"{$refsize[0]}\" height=\"{$refsize[1]}\">"));
	}

}
?>
