<?php

class ImageGenerate {

    public static function generateMockImageBase64($w, $h)
    {
        $imgid = imagecreate($w, $h);
        $background = imagecolorallocate($imgid, 255, 204, 102);
        $line_colour = imagecolorallocate($imgid, 255, 255, 255);
        imagesetthickness ($imgid, 2);
        imageline($imgid, 0, $h, $w, 0, $line_colour);
        ob_start();
        imagepng($imgid);
        $image_data = ob_get_contents();
        ob_end_clean ();
        imagecolordeallocate($imgid, $line_colour);
        imagecolordeallocate($imgid, $background);
        imagedestroy($imgid);
        return 'data:image/png;base64,' . base64_encode($image_data);
    }

    public static function generateMockImageFile($path, $w, $h)
    {
        $imgid = imagecreate($w, $h);
        $background = imagecolorallocate($imgid, 255, 204, 102);
        $line_colour = imagecolorallocate($imgid, 255, 255, 255);
        imagesetthickness ($imgid, 2);
        imageline($imgid, 0, $h, $w, 0, $line_colour);
        imagepng($imgid, $path);
        imagecolordeallocate($imgid, $line_colour);
        imagecolordeallocate($imgid, $background);
        imagedestroy($imgid);
        return true;
    }

}
?>