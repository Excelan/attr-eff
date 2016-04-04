<?php

function twitterGetTimeline($count, $username, $includereplies=true)
{
   $url = "http://api.twitter.com/1/statuses/user_timeline.json?screen_name=$username&count=$count";
   $tweets = json_decode(file_get_contents($url),TRUE);
   return $tweets;
}

function twitterGetTimelineDateText($count, $username, $includereplies=true)
{
	$tweetsF = array();
	$tweets = twitterGetTimeline($count, $username);
	foreach ($tweets as $t)
	{
		if (!$includereplies && $t['in_reply_to_user_id'])
			$pass;
		else	
			$tweetsF[strtotime($t['created_at'])] = $t['text'];
	}
	return $tweetsF;
}

?>