<?php

class OAuth1Control extends WebApplication implements ApplicationFreeAccess, ApplicationUserOptional
{

	function exclusive()
	{
		Log::debug($_POST,'oauth1');
	}	

}
?>