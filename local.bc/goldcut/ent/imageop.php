<?php


class ImageOp extends Image
{

    public static function overlay($in, $overlay, $options='')
    {
        $command = "$options {$overlay} {$in} {$in}"; // -compose bumpmap -tile
        return self::execute($command, 'composite');
    }

    public static function crop($in, $crop, $out=false)
	{
		$crop = "-crop {$crop}";
		if ($out===false)	$out = $in;
		$command = "\"$in\" $crop \"$out\"";
		return self::execute($command);
	}

	public static function rotate($in, $direction)
	{
		if ($direction == 'left') $c = "-rotate -90";
		if ($direction == 'right') $c = "-rotate 90";
		if ($direction == '180') $c = "-rotate 180";
		$command = "\"$in\" $c \"$in\"";
		return self::execute($command);
	}

	public static function unsharp($in)
	{
		$refsize = getimagesize($in);
		$ss = "0.7";
		if (($refsize[0] * $refsize[1]) < 13000000) $ss = "9";
		if (($refsize[0] * $refsize[1]) < 10000000) $ss = "6";
		if (($refsize[0] * $refsize[1]) < 8000000) $ss = "4";
		if (($refsize[0] * $refsize[1]) < 6000000) $ss = "5";
		if (($refsize[0] * $refsize[1]) < 5000000) $ss = "3.5";
		if (($refsize[0] * $refsize[1]) < 3000000) $ss = "2.5";
		if (($refsize[0] * $refsize[1]) < 2000000) $ss = "1.5";
		if (($refsize[0] * $refsize[1]) < 1000000) $ss = "0.7";
		if (($refsize[0] * $refsize[1]) < 500000) $ss = "0.4";
		if (($refsize[0] * $refsize[1]) < 10000) $ss = "0.2";
		$s = "-unsharp 5x1.5+{$ss}+0.0";
		$command = "\"$in\" $s \"$in\"";
		self::execute($command);
		return $ss;
	}

	public static function trim($in)
	{
		$c = "-fuzz 10% -trim +repage";
		$command = "\"$in\" $c \"$in\"";
		return self::execute($command);
	}
	
	
	
}

?>