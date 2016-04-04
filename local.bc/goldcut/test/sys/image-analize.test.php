<?php
require dirname(__FILE__).'/../../../goldcut/boot.php';

/**
	dark dress
	0000000000000000000001100001010000000000000000000000000000000000
	1100000100000010011011101000011110000000000000000000000000000010
	1101110000000000000000000000000000000000000000000000000000000000

	violet
	0000000000000000000000000000000000100000000000000000000000001110
	1111000000000000000000000000000110000000000000000000000000000000
	0111100000000000000000000000000000000000000000000000000000000000

	dress1 violet
	0000000000000000000010001000000011000000000000000000000000001101
	1111000000000000000000000000011011101000000000000000000000000000
	0011111100000000000000000000000000000000000000000000000000000000
	
	biruza dress
	0000000000000000000000000000000000000000000000000000000000000000
	0011110000011111000000000000000000000000000000011000000000000000
	0011110110000000000000000000000000000000000000000000000000000000
	
	nude
	1000000000000000000000000000000011000000000000000000000000000000
	1100000000000000000100000000010011000000000000000001000000000001
	0100000000000000001100000000000001100000000000000010000000000000
	
	0000000000000000000000000000000000000000000000000000000000000000
	1000000000000000000000000000000011000000000000000000000000000000
	1110000000000100111000000000000101000000000000000000000000000000
	
	0000000000000000000000000000000000100010000000000000000000000000
	0000000000000000000000000000000111000010000000000000000001000010
	0010100000000001110000000000000011100000000000000100000000000001
	
	0000000000000000000000000000000011000000000000000000000000000000
	0000000000000000000000000000000001000000000000000000000000000000
	1110000000000000000000000000001101000000000000000000000000000000
	
	body
	0100000000000000000000000000000000100000000000000000000000000000
	0010000000000000000000000000000001100000000000000000000000000000
	0011000000000000000000000000000001100000000000000000000000000000
	
	dress 6-7
	0000000000000001110000000000000000000000000000000000000000000000
	0011010001101111110000000000000001000000000000000000000000000000
	0010000000000000010000000000000000000000000000000000000000000000
	
	0001001101100110000000000000000001000000000000000000000000000000
	0010100101011111000000000000000000100000000000000000000000000000
	0000000000001000000000000000000000100000000000000000000000000000
     *
     *
     * EXAMPLE
     * 0000000000000000000000000000000000000000000000000000000000000000
    [1]:[1]:[0]35,5 - middle V full S Red H color weight is 35
    [1]:[1]:[1]14
    [1]:[1]:[2]8
    [1]:[1]:[31]27,5
    0000000000000000000000000000000011100000000000000000000000000001
    [2]:[1]:[0]17,5
    [2]:[1]:[1]2,5
    [2]:[1]:[31]2
    0000000000000000000000000000000011000000000000000000000000000001
    Color saturated area (dark black is not saturated!) 64
    Dark colors area (not BW pixels) 63
    Image bits (from dark color to light colors top/bottom. non saturated/saturated tints left 32 / right 32)
    1010001011111111111111111111111111011111101111111011111100101110
	*/
    /*
    HSV color model
    Hue color circle. from violet red as 1 to right 0 > blue, green, yelow, orange, red
    Saturation from 0 White to H (fully saturated Tint Color)
    Value from 0 black to middle tint, Проявленность цвета. 0 - black, 1 - lightest color
    */
/*
 * <field name="freeareas" />
        <field name="histogram64" />
        <field name="binaryimage" />
        <field name="color" />
        <field name="color2" />
 */

//define('DEBUG_IMAGEANALIZE', true);

class IATest implements TestCase
{
    private function printBox($hsv)
    {
        $rgb = Lux_Color::hsv2rgb(array($hsv[0],$hsv[1],$hsv[2]));
        $hexcolor = sprintf("%02X%02X%02X", floor($rgb[0]), floor($rgb[1]), floor($rgb[2]));
        print("<div style='width: 50px; height: 50px; background-color: #{$hexcolor}'>&nbsp;</div>");
    }

    function xp()
    {
        $file = BASE_DIR.'/tmp/xp.jpg';
        $image = new ImageAnalize($file);
        $image->areaDistribution();
        //println("Image bits (from dark color to light colors top/bottom. non saturated/saturated tints left 32 / right 32)",1,TERM_GRAY);
        println($image->bits);
        println($image->freeareas);
        //println($image->HSVgrid,1,TERM_VIOLET); // all colors
        $mainColors = $image->mainColor();
        $this->printBox($mainColors[0]);
        $this->printBox($mainColors[1]);
        //println($mainColors,1,TERM_RED);
        println($image->hist3x64,1,TERM_GRAY);
        print "<img src='/tmp/32-ANALIZE-xp.jpg' width='64' height='64'>";
        print "<img src='/tmp/32-BW-xp.png' width='64' height='64'>";
    }

    function children()
    {
        $file = BASE_DIR.'/tmp/children.jpg';
        $image = new ImageAnalize($file);
        $image->areaDistribution();
        println("Image bits (from dark color to light colors top/bottom. non saturated/saturated tints left 32 / right 32)",1,TERM_GRAY);
        println($image->bits);
        println($image->freeareas);
        //println($image->HSVgrid,1,TERM_VIOLET); // all colors
        $mainColors = $image->mainColor();
        $this->printBox($mainColors[0]);
        $this->printBox($mainColors[1]);
        println($image->hist3x64,1,TERM_VIOLET);
        print "<img src='/tmp/32-ANALIZE-children.jpg' width='64' height='64'>";
        print "<img src='/tmp/32-BW-children.png' width='64' height='64'>";
    }

    function reddress()
    {
        $file = BASE_DIR.'/tmp/reddress.jpg';
        $image = new ImageAnalize($file);
        $image->areaDistribution();
        //println("Image bits (from dark color to light colors top/bottom. non saturated/saturated tints left 32 / right 32)",1,TERM_GRAY);
        println($image->bits);
        println($image->freeareas);
        //println($image->HSVgrid,1,TERM_VIOLET); // all colors
        $mainColors = $image->mainColor();
        $this->printBox($mainColors[0]);
        $this->printBox($mainColors[1]);
        //println($mainColors,1,TERM_RED);
        println($image->hist3x64,1,TERM_VIOLET);
        print "<img src='/tmp/32-ANALIZE-reddress.jpg' width='64' height='64'>";
        print "<img src='/tmp/32-BW-reddress.png' width='64' height='64'>";
    }

    function dressbb()
    {
        $file = BASE_DIR.'/tmp/dressbb.jpg';
        $image = new ImageAnalize($file);
        $image->areaDistribution();
        //println("Image bits (from dark color to light colors top/bottom. non saturated/saturated tints left 32 / right 32)",1,TERM_GRAY);
        println($image->bits);
        println($image->freeareas);
        //println($image->HSVgrid,1,TERM_VIOLET); // all colors
        $mainColors = $image->mainColor();
        $this->printBox($mainColors[0]);
        $this->printBox($mainColors[1]);
        //println($mainColors,1,TERM_RED);
        println($image->hist3x64,1,TERM_VIOLET);
        print "<img src='/tmp/32-ANALIZE-dressbb.jpg' width='64' height='64'>";
        print "<img src='/tmp/32-BW-dressbb.png' width='64' height='64'>";
    }

	function manymodelsinred()
	{
		$file = BASE_DIR.'/tmp/model.jpg';
        $image = new ImageAnalize($file);
		$image->areaDistribution();
        println("Image bits (from dark color to light colors top/bottom. non saturated/saturated tints left 32 / right 32)",1,TERM_GRAY);
		println($image->bits);
        println($image->freeareas);
        //println($image->HSVgrid,1,TERM_VIOLET); // all colors
        $mainColors = $image->mainColor();
        $this->printBox($mainColors[0]);
        $this->printBox($mainColors[1]);
        println($image->hist3x64,1,TERM_VIOLET);
        print "<img src='/tmp/32-ANALIZE-model.jpg' width='64' height='64'>";
		print "<img src='/tmp/32-BW-model.png' width='64' height='64'>";
	}

    /**
     [2]:[0]:[7]18 - head body color
     */
    function modelindarkdress()
    {
        $file = BASE_DIR.'/tmp/model2.jpg';

        $image = new ImageAnalize($file);
        $image->areaDistribution();

        println("image bits (from dark color to light colors top/bottom. non saturated/saturated tints left 32 / right 32)");
        println($image->bits);
        println($image->freeareas);
        $mainColors = $image->mainColor();
        $this->printBox($mainColors[0]);
        $this->printBox($mainColors[1]);
        println($image->hist3x64,1,TERM_VIOLET);

        print "<img src='/tmp/32-ANALIZE-model2.jpg' width='64' height='64'>";
        print "<img src='/tmp/32-BW-model2.png' width='64' height='64'>";
    }

    function rainbow()
    {
        $file = BASE_DIR.'/tmp/rainbow.jpg';

        $image = new ImageAnalize($file);
        $image->areaDistribution();

        println("image bits");
        println($image->bits);
        println($image->freeareas);
        $mainColors = $image->mainColor();
        $this->printBox($mainColors[0]);
        $this->printBox($mainColors[1]);
        println($image->hist3x64,1,TERM_VIOLET);

        print "<img src='/tmp/32-ANALIZE-rainbow.jpg' width='64' height='64'>";
        print "<img src='/tmp/32-BW-rainbow.png' width='64' height='64'>";
    }

    /**
    [1]:[1]:[20]9 - middle-dark blue box in center. 0000000000000000000000000000000010001000100100010000100100010000 - vivid rainbow
     */
    function rainbow2()
    {
        $file = BASE_DIR.'/tmp/rainbow2.jpg';

        $image = new ImageAnalize($file);
        $image->areaDistribution();

        println("image bits");
        println($image->bits);
        println($image->freeareas);
        $mainColors = $image->mainColor();
        $this->printBox($mainColors[0]);
        $this->printBox($mainColors[1]);
        println($image->hist3x64,1,TERM_VIOLET);

        print "<img src='/tmp/32-ANALIZE-rainbow2.jpg' width='64' height='64'>";
        print "<img src='/tmp/32-BW-rainbow2.png' width='64' height='64'>";
    }

    function checker()
    {
        $file = BASE_DIR.'/tmp/checker.jpg';

        $image = new ImageAnalize($file);
        $image->areaDistribution();

        println("image bits");
        println($image->bits);
        println($image->freeareas);
        $mainColors = $image->mainColor();
        $this->printBox($mainColors[0]);
        $this->printBox($mainColors[1]);
        println($image->hist3x64,1,TERM_VIOLET);

        print "<img src='/tmp/32-ANALIZE-checker.jpg' width='64' height='64'>";
        print "<img src='/tmp/32-BW-checker.png' width='64' height='64'>";
    }

    /**
     В [1]:[0]:[0] попадает средний серый. а не красный. [0]:[0]:[0]32 - черный. [2]:[1]:[0]16 - светлый яркий красный
     */
    function cb()
    {
        $file = BASE_DIR.'/tmp/cb.jpg';

        $image = new ImageAnalize($file);
        $image->areaDistribution();

        println("image bits");
        println($image->bits);
        println($image->freeareas);
        $mainColors = $image->mainColor();
        $this->printBox($mainColors[0]);
        $this->printBox($mainColors[1]);
        println($image->hist3x64,1,TERM_VIOLET);

        print "<img src='/tmp/32-ANALIZE-cb.jpg' width='64' height='64'>";
        print "<img src='/tmp/32-BW-cb.png' width='64' height='64'>";
    }




    private function hsvtest()
    {
        /*
         * //$rgb = Lux_Color::hsv2rgb(array($hsv[0],$hsv[1],$hsv[2]));
				//$color = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
		for($i=0;$i<=255;$i++)
		{
			$rgb = array($i,0,$i);
			$hsv = Lux_Color::rgb2hsv($rgb);
			$rgb = Lux_Color::hsv2rgb($hsv);
			$color = Lux_Color::rgb2hex($rgb);
			print("<div style='float: left; width: 30px; height: 30px; background-color: #{$color}'>&nbsp;</div>");
			$hsv[0] = floor($hsv[0]*360);
			$hsv[1] = floor($hsv[1]*100);
			$hsv[2] = floor($hsv[2]*100);
			println($hsv);
			println($rgb,2); // $H*360, $S*100, $V*100
		}
        */
        foreach ($a as $c)
        {
            //println($c);
            //$hsv = Lux_Color::rgb2hsv($rgb);
            //$color = $c[0]['rgbHex'];
            //print("<div style='float: left; width: 30px; height: 30px; background-color: #{$color}'>&nbsp;</div>");
            ////$hex =sprintf("%02X%02X%02X", $colorArray[$x][$y]['r'], $colorArray[$x][$y]['g'], $colorArray[$x][$y]['b']);
        }
    }
}
?>