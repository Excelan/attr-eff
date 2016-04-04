<?php

/*
HSV color model
Hue color circle. from violet red as 1 to right 0 > blue, green, yelow, orange, red
Saturation from 0 White to H (fully saturated Tint Color)
Value from 0 black to middle tint, Проявленность цвета. 0 - black, 1 - lightest color
*/
class ImageAnalize 
{
	/**
	Tone clusters
	histogram f=of HS pairs
	
	center weighted color distr
	
	Similarity
	by color hist distance
	by area of main colors
	by bw binary (light form)
	
	4 x 64bit image areas for 16x16px bw hash
	bit shift 1left, 1 right & compare, shift 8 up/down 
	*/

    public $useweights = false;
    public $saturated; // count from 64
    public $darkpixels; // count from 64
    public $freeareas;
    public $name;
	public $path;
	private $resource;
	private $uniform;
	public $width = 8; // TODO count colors in 32x32, store in 8x8
	public $height = 8;
	public $widthOriginal;
	public $heightOriginal;
	public $HSVgrid = array(); // SV (0-1,0-1,0-1), при конвертации *360,*100,*100 > H 0-360 для цветового тона, 0-100 для яркости и насыщенности
	public $histogram; // color weights in VSH matrix
    public $hist3x64; // 3 rows with 32+32 color wheels
	public $center;
	public $faces;
	public $transparent;
	public $singleToneBackground;
	public $nudityFactor;
	public $bits = '';

    public static function hsv2rgbhex($hsv)
    {
        $rgb = Lux_Color::hsv2rgb(array($hsv[0],$hsv[1],$hsv[2]));
        $hexcolor = sprintf("%02X%02X%02X", floor($rgb[0]), floor($rgb[1]), floor($rgb[2]));
        return $hexcolor;
    }

	public function __construct($path)
	{
        $filename = Utils::filename_extension($path);
        $this->name = $filename['name'];
		$this->path = $path;
		$this->resource = imagecreatefromjpeg($this->path);
		$this->widthOriginal = imagesx($this->resource);
		$this->heightOriginal = imagesy($this->resource);
		$this->transparent = imagecolortransparent($this->resource);
		$this->prepareUniformCanvas();
		$this->analyzeImageColors(); // make HSVgrid
		$this->bw(); // analize light vs dark areas. 0 is black. V < 0.5 is black
		$this->colorHist(); // analize colors???
	}
	
	private function prepareUniformCanvas()
	{
		$this->uniform = imagecreatetruecolor($this->width, $this->height);
        imagecopyresized($this->uniform, $this->resource, 0, 0, 0, 0, $this->width, $this->height, $this->widthOriginal, $this->heightOriginal); // imagecopyresampled (sharpen, color edges got darken)
        if (DEBUG_IMAGEANALIZE === true)
        {
		    $file = BASE_DIR.'/tmp/32-ANALIZE-'.$this->name.'.jpg';
		    imagejpeg($this->uniform,$file,100);
        }
	}
	
	private function analyzeImageColors()
	{
		$im = $this->uniform; // $this->resource;
		for ($y = 0; $y < $this->height; $y++) // scan vertical lines
		{
			for($x = 0; $x < $this->width; $x++) // scan each pixel from left to right in line
			{
				$rgbint = imagecolorat($im, $x, $y);
				$r = ($rgbint >> 16) & 0xFF;
				$g = ($rgbint >> 8) & 0xFF;
				$b = $rgbint & 0xFF;
				$rgb = array($r,$g,$b);
				$hsv = Lux_Color::rgb2hsv($rgb);
				$this->HSVgrid[$x][$y] = $hsv;
			}
		}
		imagedestroy($im);
	}	
	
	private function bw()
	{
		$HSVgrid = $this->HSVgrid;
		$im = imagecreate(8, 8);
		$white = imagecolorallocate($im, 255, 255, 255);
		$black = imagecolorallocate($im, 0, 0, 0);
		for ($y = 0; $y < $this->height; $y++) // scan vertical lines
		{
			for($x = 0; $x < $this->width; $x++) // scan each pixel from left to right in line
			{
                // row 1, 3 cols TODO disbalance - 00011122?
                if ($x <= 2 && $y <= 2) $area = 0;
                else if ($x > 2 && $x <= 5 && $y <= 2) $area = 1;
                else if ($x > 5 && $y <= 2) $area = 2;
                // row 2
                else if ($x <= 2 && $y <= 5) $area = 3;
                else if ($x > 2 && $x <= 5 && $y <= 5) $area = 4;
                else if ($x > 5 && $y <= 5) $area = 5;
                // row 3
                else if ($x <= 2 && $y <= 7) $area = 6;
                else if ($x > 2 && $x <= 5 && $y <= 7) $area = 7;
                else if ($x > 5 && $y <= 7) $area = 8;

                //println("$x x $y a: $area");
				$hsv = $HSVgrid[$x][$y];
				if ($hsv[2] < 0.5) // TODO MAKE SENSIBLE
				{
					imagesetpixel($im, $x, $y, $black);
					$this->bits .= '0';
                    //if ()
                    {
                        $this->freeareas[$area]++;
                    }
				}
				else
				{
					imagesetpixel($im, $x, $y, $white);
					$this->bits .= '1';
                    //$this->freeareas[$area] = 0;
                    //println("$x $y");
                    // freeareas
				}
			}
		}
        foreach ($this->freeareas as $area => $contentcount)
        {
            if ($contentcount > 3) $this->freeareas[$area] = 1;
            else $this->freeareas[$area] = 0;
        }
        ksort($this->freeareas);
        if (DEBUG_IMAGEANALIZE === true)
        {
		    $file = BASE_DIR.'/tmp/32-BW-'.$this->name.'.png';
		    imagepng($im,$file);
            imagedestroy($im);
        }
        /*
        $this->freeareas[0] = 0;
        // 0..2 5..7  0..2 5..7
        for ($base=0; $base<3; $base++)
        {
            $i = $base*8;
            $this->freeareas[0] += (int) $this->bits[0+$i];
            $this->freeareas[0] += (int) $this->bits[1+$i];
            $this->freeareas[0] += (int) $this->bits[2+$i];
            //$this->freeareas[0] += (int) $this->bits[5+$i];
            //$this->freeareas[0] += (int) $this->bits[6+$i];
            //$this->freeareas[0] += (int) $this->bits[7+$i];
        }
        */
	}

	private function colorHist()
	{
		$HSVgrid = $this->HSVgrid;
        $hist = array();
        $histBinary = array();

        // historgam VSH - 3 Values - 2 light, 1 middle, 0 dark. 2 Saturations - AlmostGray, Tint - 32 Color Tints
        // $hist[$v3][$s2][$h32]
		for ($y = 0; $y < $this->height; $y++) // scan lines from top to bottom
		{
            $i = 0;
			for($x = 0; $x < $this->width; $x++) // scan each pixel from left to right in line
			{
				$i++;
				$hsv = $HSVgrid[$x][$y];
                //println($hsv);
				$h32 = floor($hsv[0] * 32); // split H to 0..31
				$s2 = ($hsv[1] > 0.25) ? 1 : 0; // saturation more then 0.25 is saturated color // old $s2 = floor($hsv[1] * 2);
				$v3 = floor($hsv[2] * 3); // split Value to 0..2
				$weightX = 1;
				$weightY = 1;
                // weight colors in center
                if ($this->useweights)
                {
				    if ($x > 1 && $x < 6) $weightX = 2; // make colors at borders less important
				    if ($y >= 3 && $y <= 4) $weightY = 1.5; // make colors center more important
                }
				$wt = 1 * $weightX * $weightY;
				$hist[$v3][$s2][$h32] += $wt; //
				//println("[$v3][$s2][$h32] += $wt");
				//println("$i  $x x $y : $h32 $s2 $v3");
			}
		}
		for ($v = 0; $v < 3; $v++) // 0, 1, 2
		{
			for ($s = 0; $s < 2; $s++) // 0, 1
			{
				for ($h = 0; $h < 32; $h++) // 0..31
				{
					if ($hist[$v][$s][$h] >= 2) 
					{
                        if (DEBUG_IMAGEANALIZE === true) println("[$v]:[$s]:[$h]".$hist[$v][$s][$h],1,TERM_GRAY);
						$histBinary[$v] .= '1';
					}
					else 
					{
						// if (DEBUG_IMAGEANALIZE === true) println("[$v]:[$s]:[$h]".$hist[$v][$s][$h],1,TERM_GRAY);
						$histBinary[$v] .= '0';
					}
					
				}	
			}
            // $histBinary is 3 x 64 lines - first - dark colors, second - middle, third - light colors
            // each 64 line - 32 binary weights of almost gray/white/subtile colors + 32 full tint colors
		}
		$this->histogram = $hist;
        $this->hist3x64 = $histBinary;
	}


    private function colorTF($h32,$SS, $VV)
    {
        $image = $this;
        $VV++;
        $H = $h32 / 32;
        $S = ($SS / 2);
        $V = ($VV / 3);
        //println("$H $S $V",1,TERM_GREEN);
        if ($image)
        {
            $S = $image->saturated/64; // * S
            //println("$H $S $V ({$image->saturated})",2,TERM_GREEN);
        }
        //$S = 1;
        $hsv = array($H, $S, $V);
        return $hsv;
    }

    public function mainColor()
    {
        $histogram = $this->histogram;
        $prevWeight = 0;
        foreach ($histogram[1][1] as $h32 => $weight)
        {
            if ($h32 == 0) continue;
            if ($weight > $prevWeight)
            {
                $maxH32 = $h32;
                $prevWeight = $weight;
            }
        }
        $h32 = $maxH32;
        //println($h32,1,TERM_YELLOW); // VSH weights matrix
        //$this->printBox($h32, 1, 1, $image);
        $color11 = $this->colorTF($h32, 1, 1);

        foreach ($histogram[2][1] as $h32 => $weight)
        {
            if ($h32 == 0) continue;
            if ($weight > $prevWeight)
            {
                $maxH32 = $h32;
                $prevWeight = $weight;
            }
        }
        $h32 = $maxH32;
        //println($h32,1,TERM_YELLOW); // VSH weights matrix
        //$this->printBox($h32, 2, 2, $image);

        $color22 = $this->colorTF($h32, 2, 2);
        return array($color11, $color22);
    }



	public function areaDistribution()
	{
        $s = 0; // satureted pixels count
        $b = 0; // dark pixles count
		$HSVgrid = $this->HSVgrid;
		for ($y = 0; $y < $this->height; $y++) // scan vertical lines
		{
			//printH($y);
			$lineB = 0;
			$lineS = 0;
			for($x = 0; $x < $this->width; $x++) // scan each pixel from left to right in line
			{
				$hsv = $HSVgrid[$x][$y];
				if ($hsv[2] < 0.85) 
				{
					//println($x);
					$lineB++;
					$b++;
				}
				if ($hsv[1] > 0.25) 
				{
					$lineS++;
					$s++;
				}
			}
			if ($lineB >= 2 && !$firstLineY) $firstLineY = $y;
			if ($lineB >= 2) $lastLineY = $y;
			//println("$y: $lineS $lineB");
		}
		for($x = 0; $x < $this->width; $x++) 
		{
			//printH($y);
			$lineB = 0;
			$lineS = 0;
			for ($y = 0; $y < $this->height; $y++)
			{
				$hsv = $HSVgrid[$x][$y];
				//printlnd($hsv);
				if ($hsv[2] < 0.85) 
				{
					$lineB++;
				}
				if ($hsv[1] > 0.25) 
				{
					$lineS++;
				}
			}
			if ($lineB >= 1 && !$firstLineX) $firstLineX = $x;
			if ($lineB >= 2) $lastLineX = $x;
			//println("$y: $lineS $lineB");
		}
		
		//dprintln("Y $firstLineY - $lastLineY, X $firstLineX - $lastLineX");
		//dprintln("Area total ". $this->width * $this->height);
        if (DEBUG_IMAGEANALIZE === true) dprintln("Color saturated area (dark black is not saturated!) $s");
        if (DEBUG_IMAGEANALIZE === true) dprintln("Dark colors area (not BW pixels) $b");
        $this->saturated = $s;
        $this->darkpixels = $b;
        //dprintln("HSV GRID:");
		//dprintln($this->HSVgrid);
	}
}
?>