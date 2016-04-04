<?php
/**
-liquid-rescale
http://www.imagemagick.org/Usage/resize/#liquid-rescale
http://serverfault.com/questions/97156/install-imagemagick-with-liquid-rescale-support (ubuntu, freebsd has port option)

USE sizes x 8 for jpeg speedup

fast thumbnail == exif thumbnail

label text
$label = "50%"; // $fontsize = 16; // $fontcolor = "#fff";
text 16,26 '{$label}' -pointsize {$fontsize} fill '{$fontcolor}' stroke none
*/
class Image
{
	
	public static function copy($in, $out)
	{
		copy($in, $out);
	}

	protected static function execute($command, $action='convert')
	{
		Utils::startTimer('image');
		$IMAGEMAGICK = ImageExternalApp::getExternalAppCommand($action); //OS::getim();
		$fc = $IMAGEMAGICK.' '.$command;
		Log::debug($fc, 'image');
		$er = system($fc, $retval);
        Log::debug($retval, 'image');
        Log::debug($er, 'image');
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		{
			if ($retval != 0) throw new Exception("Failed to ImageMagick convert $command - $er\n");
		}
		return Utils::reportTimer('image');
	}

	public static function size($fileuri, $withfilesize=false)
	{
		$imagesize = array();
		$size = getimagesize($fileuri);
		$imagesize['width'] = $size[0];
		$imagesize['height'] = $size[1];
		if ($withfilesize)
		{
			$filesize = filesize($fileuri);
			$imagesize['filesize'] = $filesize;
		}
		if (!$imagesize['width'])
			return null;
		else
			return $imagesize;
	}

	public static function analizeImageParadigm($in)
    {
		list($w,$h) = getimagesize($in);
		$isLandscape = $w > $h;
//		$ratio = $w / $h;
		$ip['minside'] = ($w > $h) ? $h : $w;
		$ip['maxside'] = ($w < $h) ? $h : $w;
		$ip['ratio'] = $ip['maxside'] / $ip['minside'];
		if ($ip['ratio'] < 1.2)
			$ip['paradigm'] = 'S';
		elseif ($ip['ratio'] <= 3 && $isLandscape)
			$ip['paradigm'] = 'HE';
		elseif ($ip['ratio'] <= 3 && !$isLandscape)
			$ip['paradigm'] = 'VE';	
		elseif ($ip['ratio'] >= 3 && $isLandscape)
			$ip['paradigm'] = 'WS';
		elseif ($ip['ratio'] >= 3 && !$isLandscape)
			$ip['paradigm'] = 'TS';	
		else throw new Exception('Unknown image paradigm');
		$ip['ow'] = $w;
		$ip['oh'] = $h;
		$ip['size']['ratio'] = $ip['ratio'];
		// $ip['size'][''] = 
        return $ip;
    }
	
	/**
	calc other side key, value. targer width, height. calc target ratio,
	if no fixed other side provieded, calc from parent parsdigm (as target main side size / parent paradigm ratio)
	*/
	public static function calcParadigmParameters(&$paradigmObject, $parentParadigm = null)
	{
		// println("Image::calcParadigmParameters",1,TERM_YELLOW);
        // println($parentParadigm,3,TERM_GRAY);
        // println($paradigmObject,2,TERM_GRAY);
		// CALC OTHER SIDE KEYM VALUE
		// HE with known VF
		if ($paradigmObject['size']['verticalfixed']) 
		{
			// println(1);
			$otherSideKey = 'verticalfixed';
			$otherSideValue = $paradigmObject['size']['verticalfixed'];
		}
		// VE with known HF
		else if ($paradigmObject['size']['horizontalfixed']) 
		{
			// println(2);
			$otherSideKey = 'horizontalfixed';
			$otherSideValue = $paradigmObject['size']['horizontalfixed'];
		}
		// if FIXED NOT KNOWN & has parentParadigm
		if (!$otherSideKey && $parentParadigm)
		{
			// println(30);
			// println($paradigmObject);
			$paradigmObject['size']['ratio'] = $parentParadigm['size']['ratio']; // used locally
			$otherSideValue = (int) round($paradigmObject['size']['size'] / $paradigmObject['size']['ratio']);
			// NOT USED!!! BUT 70 > 71 WORKED
			if ($parentParadigm['size']['verticalfixed']) 
			{
				// println(31, 1, TERM_RED);
				$otherSideKey = 'verticalfixed';
			}
			// NOT USED!!!	
			else if ($parentParadigm['size']['horizontalfixed']) 
			{
				// println(32, 1, TERM_RED);
				$otherSideKey = 'horizontalfixed';
			}
			
		}
		// CALC WIDTH, HEIGHT
		if ($paradigmObject['size']['dim'] == 'horizontal') 
		{
			// println(4);
			$width = $paradigmObject['size']['size'];
			$height = $otherSideValue;
		}
		else if ($paradigmObject['size']['dim'] == 'vertical') 
		{
			// println(5);
			$width = $otherSideValue;
			$height = $paradigmObject['size']['size'];
		}
		else if ($paradigmObject['size']['dim'] == 'eachside') 
		{
			// println(6);
			$width = $paradigmObject['size']['size'];
			$height = $paradigmObject['size']['size'];
		}
		else if ($paradigmObject['size']['dim'] == 'largestside') 
		{
			// println(70);
			// println($parentParadigm['paradigm'],1,TERM_RED);
			if ($parentParadigm['paradigm'] == 'HE' || $parentParadigm['paradigm'] == 'WS')
			{
				// println(71);
				$width = $paradigmObject['size']['size'];
				$height = (int) round($paradigmObject['size']['size'] / $paradigmObject['size']['ratio']);
			}
			else if ($parentParadigm['paradigm'] == 'VE' || $parentParadigm['paradigm'] == 'TS')
			{
				// println(72);
				$width = (int) round($paradigmObject['size']['size'] / $paradigmObject['size']['ratio']);
				$height = $paradigmObject['size']['size'];
			}
			else if ($parentParadigm['paradigm'] == 'C' || $parentParadigm['paradigm'] == 'S')
			{
				// println(73);
				$width = (int) round($paradigmObject['size']['size'] / $paradigmObject['size']['ratio']);
				$height = $paradigmObject['size']['size'];
			}
			else 
			{
				throw new Exception("Unimplemented calc WxH for {$parentParadigm['paradigm']}");
			}
		}
		
		if ($paradigmObject['paradigm'] == 'S') 
		{
			// println(8);
			$otherSideKey = null;
			$otherSideValue = $paradigmObject['size']['size'];
		}
		
		$paradigmObject['size']['otherSideKey'] = $otherSideKey;
		$paradigmObject['size']['otherSideValue'] = $otherSideValue;
		$paradigmObject['size']['width'] = $width;
		$paradigmObject['size']['height'] = $height;
		//if (!$paradigmObject['size']['ratio']) 
		//if (!$paradigmObject['size']['ratio']) 
		$paradigmObject['size']['ratio'] = $paradigmObject['size']['size'] / $otherSideValue;
		if (!$width || !$height || !$paradigmObject['size']['ratio']) 
		{
			// println($paradigmObject,1,TERM_RED);
			throw new Exception("calcParadigmParameters !width {$width} || !height {$height} || !paradigmObject[size][ratio] {$paradigmObject['size']['ratio']}");
		}
        // println($paradigmObject,1,TERM_GRAY);
	}
	
	/*
	для crop отступ будет слева или сверху?
	ошибка если crop больше, чем исходное изображение
	diffRatio - 
	targetRatio - 
	$targetRatio > $originalParadigm['ratio'] - 
	minTargetRatio
	maxTargetRatio
	minCrop
	maxCrop
	*/
	public static function analizeCropPossibilitiesBetweenImageParadigms($originalParadigm, $targetParadigm)
	{
        //dprintln("Image::analizeCropPossibilitiesBetweenImageParadigms(originalParadigm, targetParadigm)", 1, TERM_YELLOW);
        //dprintln($originalParadigm, 1);
        //dprintln($targetParadigm, 1);
		
		$cropoffset = null;
		
		// HE > HE
		if ($originalParadigm['paradigm'] == 'HE' && $targetParadigm['paradigm'] == 'HE')
		{
			 //dprintln("HE > HE",1,TERM_YELLOW);
			if ($targetParadigm['size']['ratio'] > $originalParadigm['ratio'])
			{
				$cropwidth = $originalParadigm['ow'];
				$cropheight = (int) round($cropwidth / $targetParadigm['size']['ratio']);
				$croprichdim = 'V';
				$cropoffset = (int) floor(($originalParadigm['oh'] - $cropheight) / 2);
			}
			else if ($targetParadigm['size']['ratio'] < $originalParadigm['ratio']) 
			{
				$croprichdim = 'H';
				$cropheight = $originalParadigm['oh'];
				$cropwidth = (int) round($cropheight * $targetParadigm['size']['ratio']);
                $cropoffset = (int) floor(($originalParadigm['ow'] - $cropwidth) / 2);
			}
			else if ($targetParadigm['size']['ratio'] == $originalParadigm['ratio'])
			{
				$croprichdim = null;
			}
		}
		// HE > VE
		else if ($originalParadigm['paradigm'] == 'HE' && $targetParadigm['paradigm'] == 'VE')
		{
            //dprintln("HE > VE",1,TERM_YELLOW);
			$croprichdim = 'H';
			$cropheight = $originalParadigm['oh'];
			$cropwidth = (int) round($cropheight / $targetParadigm['size']['ratio']);
			$cropoffset = (int) floor(($originalParadigm['ow'] - $cropwidth) / 2);
		}
		// HE > S
		else if ($originalParadigm['paradigm'] == 'HE' && $targetParadigm['paradigm'] == 'S')
		{
            //dprintln("HE > S",1,TERM_YELLOW);
			$croprichdim = 'H';
			$cropheight = $originalParadigm['oh'];
			$cropwidth = $originalParadigm['oh'];
			$cropoffset = (int) floor(($originalParadigm['ow'] - $cropwidth) / 2);
		}
		
		// VE > HE
		else if ($originalParadigm['paradigm'] == 'VE' && $targetParadigm['paradigm'] == 'HE')
		{
            //dprintln("VE > HE",1,TERM_YELLOW);
			$cropwidth = $originalParadigm['ow'];
			$cropheight = (int) round($cropwidth / $targetParadigm['size']['ratio']);
			$croprichdim = 'V';
			$cropoffset = (int) floor(($originalParadigm['oh'] - $cropheight) / 2);
		}
		// VE > VE
		else if ($originalParadigm['paradigm'] == 'VE' && $targetParadigm['paradigm'] == 'VE')
		{
            //dprintln("VE > VE",1,TERM_YELLOW);
			if ($targetParadigm['size']['ratio'] > $originalParadigm['ratio']) 
			{
				$croprichdim = 'H';
				$cropheight = $originalParadigm['oh'];
				$cropwidth = (int) round($cropheight / $targetParadigm['size']['ratio']);
				$cropoffset = (int) floor(($originalParadigm['ow'] - $cropwidth) / 2);				
			}
			else if ($targetParadigm['size']['ratio'] < $originalParadigm['ratio']) 
			{
				$cropwidth = $originalParadigm['ow'];
				$cropheight = (int) round($cropwidth * $targetParadigm['size']['ratio']);
				$croprichdim = 'V';
				$cropoffset = (int) floor(($originalParadigm['oh'] - $cropheight) / 2);
			}
			else if ($targetParadigm['size']['ratio'] == $originalParadigm['ratio'])
			{
				$croprichdim = null;
			}
		}
		// VE | TS > S
		else if (($originalParadigm['paradigm'] == 'VE' || $originalParadigm['paradigm'] == 'TS') && $targetParadigm['paradigm'] == 'S')
		{
            //dprintln("VE > S",1,TERM_YELLOW);
			$cropwidth = $originalParadigm['ow'];
			$cropheight = (int) round($cropwidth / $targetParadigm['size']['ratio']);
			$croprichdim = 'V';
			$cropoffset = (int) floor(($originalParadigm['oh'] - $cropheight) / 2);
		}
        /*
         * FROM S
        */
        // S > HE
        else if ($originalParadigm['paradigm'] == 'S' && $targetParadigm['paradigm'] == 'HE')
        {
            //dprintln("S > HE",1,TERM_YELLOW);
            $cropwidth = $originalParadigm['ow'];
            $cropheight = (int) round($cropwidth / $targetParadigm['size']['ratio']);
            $croprichdim = 'V';
            $cropoffset = (int) floor(($originalParadigm['oh'] - $cropheight) / 2);
        }
        // S > VE
        else if ($originalParadigm['paradigm'] == 'S' && $targetParadigm['paradigm'] == 'VE')
        {
            //dprintln("S > VE",1,TERM_YELLOW);
            $croprichdim = 'H';
            $cropheight = $originalParadigm['oh'];
            $cropwidth = (int) round($cropheight / $targetParadigm['size']['ratio']);
            $cropoffset = (int) floor(($originalParadigm['ow'] - $cropwidth) / 2);
        }
        // S > S
        else if ($originalParadigm['paradigm'] == 'S' && $targetParadigm['paradigm'] == 'S')
        {
            //dprintln("S > S",1,TERM_YELLOW);
            $croprichdim = null;
        }

        /*
		* any > C
        */
		else if ($targetParadigm['paradigm'] == 'C')
		{
            //dprintln("C > C",1,TERM_YELLOW);
			$croprichdim = null;
		}
		else 
		{
			throw new Exception("No match Paradigm {$originalParadigm['paradigm']} > Paradigm {$targetParadigm['paradigm']}");
		}
		
		/*
		if ($originalParadigm['paradigm'] == '' && $targetParadigm['paradigm'] == 'S')
		{
			$targetOtherSideSize = $targetParadigm['size']['size'];
			$diffRatio = $originalParadigm['minside'] / $targetParadigm['size']['size'];
		}
		if ($originalParadigm['paradigm'] == '' && $targetParadigm['paradigm'] == 'C')
		{
			$diffRatio = $originalParadigm['maxside'] / $targetParadigm['size']['size'];
			$targetOtherSideSize = $originalParadigm['minside'] / $diffRatio;
		}
		*/
		// $targetRatio = $targetParadigm['size']['size'] / $targetOtherSideSize;
		// println("targetOtherSideSize $targetOtherSideSize, tp-size-size {$targetParadigm['size']['size']}",3,TERM_GREEN);
		// $diffparadigm['targetwidth'] = (int) $diffparadigm['cropwidth'];
		// $diffparadigm['targetheight'] = (int) $diffparadigm['cropheight'];
		$diffparadigm = array('croprichdim'=>$croprichdim,'cropoffset'=>$cropoffset,'cropwidth'=>$cropwidth,'cropheight'=>$cropheight);
		return $diffparadigm;
	}


}

?>