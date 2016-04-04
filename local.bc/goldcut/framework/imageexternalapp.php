<?php

class ImageExternalApp
{

	public static function paradigmDiffAndTargetToCropResizeCommand($dp, $targetParadigm)
	{
		$commandStack = array();
		// crop param
		$offsetX = 0;
		$offsetY = 0;
		if ($dp['croprichdim'] == 'H') $offsetX = $dp['cropoffset'];
		if ($dp['croprichdim'] == 'V') $offsetY = $dp['cropoffset'];
		$crop = "{$dp['cropwidth']}x{$dp['cropheight']}+{$offsetX}+{$offsetY}";
		if ($dp['cropwidth'] || $dp['cropheight'])
			array_push($commandStack, array('-crop' => $crop));

		// resize param
		if ($targetParadigm['paradigm'] == 'VE')
		{
			$resize = "x".$targetParadigm['size']['size'];
		}
		if ($targetParadigm['paradigm'] == 'HE')
		{
			$resize = $targetParadigm['size']['size']."x";
		}
		if ($targetParadigm['paradigm'] == 'S')
		{
			$resize = "x".$targetParadigm['size']['size'];
		}
		if ($targetParadigm['paradigm'] == 'C')
		{
			$resize = $targetParadigm['size']['size']."x".$targetParadigm['size']['size'];
		}
        $resize .= '\>';
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') $resize = "\"{$resize}\"";
		array_push($commandStack, array('-resize' => $resize));
		return $commandStack;
	}

	/**
	TODO add bw, trim
	*/
	private static function defaultParams()
	{
		$commandParams = array();

		// $command = "-resize"; // $command = "-thumbnail"; -sample
		// if (IMAGE_FAST !== true) $unsharp = "-unsharp 0x0.2";

		$quolity = (JPEG_QUALITY > 0) ? JPEG_QUALITY : 80;
		array_push($commandParams, array("-quality" => "{$quolity}"));

		if (IMAGE_CONVERTER != 'gm') $strip = "-strip";
		else $strip = '+profile "*"';

		// -extent 100x100 apply the image to a 100x100 canvas

		// IM -define jpeg:size=200x200 (for crop 100x100)
		// GM -size 200x200 tells the jpeg decoder we only need this resolution so it can save memory and read the source image faster

		array_push($commandParams, array("-filter" => "cubic")); // Lanczos Gaussian

		if (IMAGE_CONVERTER !== 'gm')
			array_push($commandParams, array("-colorspace" => "sRGB"));
		else
			array_push($commandParams, array("-colorspace" => "RGB"));

		return $commandParams;
	}

	public static function processImage($in, $out, array $commandParams)
	{
//        println($commandParams);
		$in = realpath($in);
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') $out = str_replace('/','\\',$out);

		foreach ($commandParams as $pkpv)
		{
			$pk = key($pkpv);
			$pv = $pkpv[$pk];
			$commandParamString .= "{$pk} {$pv} ";
		}

		foreach (self::defaultParams() as $pkpv)
		{
			$pk = key($pkpv);
			$pv = $pkpv[$pk];
			$commandParamString .= "{$pk} {$pv} ";
		}

		$command = "\"$in\" $commandParamString \"$out\"";
		if (DEBUG_IMAGE === true) dprintln($command, 1, TERM_GRAY);
		self::execute($command);
	}

	protected static function execute($command)
	{
		Utils::startTimer('image');
		$IMAGEMAGICK = self::getExternalAppCommand();
		$fc = $IMAGEMAGICK.' '.$command;

		Log::debug($command, 'image');

		$er = system($fc, $retval);
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		{
			if ($retval != 0) throw new Exception("Failed to ImageMagick convert (shell returned $retval) $er $fc");
		}
		return Utils::reportTimer('image');
	}


	public static function getExternalAppCommand($action='convert')
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			// ON WIDDOWS convert.exe is system program!
			//if (IMAGE_CONVERTER == 'gm')
				$IMAGEMAGICK = 'gm.exe '.$action;
			//else
				//$IMAGEMAGICK = 'convert.exe'; // only with absolute path to bin!
		}
		elseif (strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN' || strtoupper(substr(PHP_OS, 0, 5)) === 'SUNOS')
		{
			if (IMAGE_CONVERTER == 'gm')
				$IMAGEMAGICK = '/opt/local/bin/gm '.$action;
				//$IMAGEMAGICK = '/usr/bin/gm '.$action;
			else
				$IMAGEMAGICK = '/opt/local/bin/convert';
		}
		elseif (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX')
		{
			$IMAGEMAGICK = '/usr/bin/convert';
			if (IMAGE_CONVERTER == 'gm')
				$IMAGEMAGICK = '/usr/bin/gm '.$action;
			else
				$IMAGEMAGICK = '/usr/bin/convert';
		}
		elseif (preg_match('BSD', PHP_OS)) 
		{
			if (IMAGE_CONVERTER == 'gm')
				//$IMAGEMAGICK = '/usr/local/bin/gm '.$action;
				$IMAGEMAGICK = '/usr/bin/gm '.$action;
			else
				$IMAGEMAGICK = '/usr/local/bin/convert';
		}
		else
		{
			throw new Exception("Unknow system. Cant detect ImageMagick/GraphicsMagick PATH\n");
		}

		if (ENV === 'DEVELOPMENT' or TEST_ENV === true)
		{
			if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
			{
				$cmd = explode(' ', $IMAGEMAGICK);
				if (!filesize($cmd[0])) throw new Exception("ImageMagick {$cmd[0]} binary not found\n");
			}
		}

		return $IMAGEMAGICK;
	}

}

?>
