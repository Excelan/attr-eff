<?php 
/**
Embeds
*/
class Embeds 
{
	public static function video($text)
	{
		// y 470, 269
		$youtube_width = (YOUTUBE_VIDEO_SIZE_W > 0) ? YOUTUBE_VIDEO_SIZE_W : 480;
		$youtube_height = (YOUTUBE_VIDEO_SIZE_H > 0) ? YOUTUBE_VIDEO_SIZE_H : 269;
		$text = preg_replace('[YOUTUBE=([a-zA-Z0-9_-]*)]', "<iframe class=\"youtube-player\" type=\"text/html\" width=\"{$youtube_width}\" height=\"{$youtube_height}\" src=\"http://www.youtube.com/embed/$1\" frameborder=\"0\"></iframe>", $text);
		$text = preg_replace('[VIMEO=([a-zA-Z0-9_-]*)]', "<iframe src=\"http://player.vimeo.com/video/$1\" width=\"480\" height=\"270\" frameborder=\"0\"></iframe>", $text);
		$text = preg_replace('[RUTUBE=([a-zA-Z0-9_-]*)]', "<OBJECT width=\"470\" height=\"353\"><PARAM name=\"movie\" value=\"http://video.rutube.ru/$1\"></PARAM><PARAM name=\"wmode\" value=\"window\"></PARAM><PARAM name=\"allowFullScreen\" value=\"true\"></PARAM><EMBED src=\"http://video.rutube.ru/$1\" type=\"application/x-shockwave-flash\" wmode=\"window\" width=\"470\" height=\"353\" allowFullScreen=\"true\" ></EMBED></OBJECT>", $text);
	
		$text = str_replace("[<iframe","<iframe", $text);
		$text = str_replace("</iframe>]","</iframe>", $text);
		$text = str_replace("[<OBJECT","<OBJECT", $text);
		$text = str_replace("</OBJECT>]","</OBJECT>", $text);
		
		return $text;
	}
}
?>