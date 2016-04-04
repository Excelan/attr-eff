<?php



class ImageFX extends Image
{

	//$trim = '-fuzz 5% -trim';
	//convert image -fuzz 1% -trim +repage result
	//The +repage removes the virtual canvas which is the size of the input and makes the output just the trimmed size.

    public static function blur($in, $radius)
    {
        $command = "{$in} -blur 0x{$radius} -modulate 100,130 {$in}"; //-gaussian 0x{$radius}
        return self::execute($command);
    }

	public static function trim($in)
	{
		$command = "{$in} -fuzz 5% -trim {$in}";
		return self::execute($command);
	}
	
	public static function bw($in)
	{
		$command = "{$in} -colorspace gray -sigmoidal-contrast 10,40% {$in}";
		return self::execute($command);
	}

	public static function bright($in)
	{
		$command = "{$in} -modulate 120 -quality 70 {$in}";
		return self::execute($command);
	}

	public static function dark($in)
	{
		$command = "{$in} -modulate 85 -quality 70 {$in}";
		return self::execute($command);
	}

	public static function contrast($in)
	{
		$command = "{$in} -sigmoidal-contrast 5,40% {$in}";
		return self::execute($command);
	}

	public static function sepia($in)
	{
		$command = "{$in} -sepia-tone 80% {$in}";
		return self::execute($command);
	}

	public static function wborder($in, $color='white')
	{
		$refsize = getimagesize($in);
		if ($refsize[0] < $refsize[1])
			$size = "4%x3%";
		else
			$size = "3%x4%";
		$origsize = "{$refsize[0]}x{$refsize[1]}";
		$command = "{$in} -bordercolor {$color} -border {$size} -resize {$origsize} {$in}";
		return self::execute($command);
	}

}
?>