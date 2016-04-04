<?php

class OAuthControl extends WebApplication implements ApplicationFreeAccess, ApplicationUserOptional
{

	function exclusive()
	{
		Log::debug($_POST,'oauth');
	}	

}
?>