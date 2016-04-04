<?php
//$title = htmlentities($title, ENT_QUOTES, "UTF-8");
//$tweet = htmlentities($tweet, ENT_QUOTES, "UTF-8");
//$jj_text = htmlentities($jj_text, ENT_QUOTES, "UTF-8");

/**
TODO http://php.net/manual/en/function.parse-url.php
*/

class Social
{
	public static function soc_icons($url, $title, $anons, $imgUrl)
	{
		if (!$anons) $anons = $title;
		
		if ($imgUrl)
		{
			$vk_img = $imgUrl; 
			$jj_img = "<a href='{$url}'><img border='0' src='{$imgUrl}'></a><br>";
		}
		
		//$jj_text = $anons;
		$jj_text = "
		<br>
		{$jj_img}
		$anons
		<br>
		<a href='".BASEURL."'>".SITE_NAME."</a>";
		
		$tweet = $title . ' ' . $url;
		
		/*
		println($url);
		println($tweet);
		println($imgUrl);
		println($jj_text);
		*/
		
		$tweet = urlencode($tweet);
		$jj_title = urlencode($title);
		$jj_text = urlencode(($jj_text)); // urlencode(htmlentities(  ))
		
		$soc .= "<a target=_blank rel=\"nofollow\" href='http://www.facebook.com/share.php?u={$url}'><img border=0 hspace=4 src='/lib/assets/socsrvicon/si4.png'></a>";
		$soc .= "<a target=_blank rel=\"nofollow\" href='http://twitter.com/home?status=$tweet'><img border=0 hspace=4 src='/lib/assets/socsrvicon/si3.png'></a>";
		$soc .= "<a target=_blank rel=\"nofollow\" href='http://vkontakte.ru/share.php?image={$vk_img}&url={$url}'><img border=0 hspace=4 src='/lib/assets/socsrvicon/si1.jpg'></a>";
		$soc .= "<a target=_blank rel=\"nofollow\" href='http://www.livejournal.com/update.bml?subject={$jj_title}&event={$jj_text}'><img hspace=4 border=0 src='/lib/assets/socsrvicon/si2.png'></a>";
		
		return $soc;
	}	
}
?>