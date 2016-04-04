<?php

class OS
{
	public static function getOS()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			return 'WIN';
		elseif (strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN')
			return 'OSX';
		elseif (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX')
			return 'LINUX';
        elseif (strtoupper(substr(PHP_OS, 0, 5)) === 'SUNOS')
            return 'SMARTOS';
		elseif (eregi('BSD', PHP_OS))
			return 'BSD';
		else
			return null;
	}

	public static function isBrowserIE()
	{
	    if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
	        return true;
	    else
	        return false;
	}

	public static function clientOS()
	{
		$OSstrings = array
		(

		'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
		'Windows 98' => '(Windows 98)|(Win98)',
		'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
		'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
		'Windows ME' => 'Windows ME',
		'Windows Server 2003' => '(Windows NT 5.2)',

		'Windows Vista' => '(Windows NT 6.0)',
		'Windows XP' => '(Windows NT 5.1)|(Windows XP)',

		'Windows 7' => '(Windows NT 7.0)',

		'Open BSD' => 'OpenBSD',
		'Free BSD' => 'FreeBSD',
		'Sun OS' => 'SunOS',

		'Linux' => '(Linux)|(X11)',

		'Mac OS' => '(Mac_PowerPC)|(Macintosh)|(OS X)',

		'QNX' => 'QNX',
		'BeOS' => 'BeOS',
		'OS/2' => 'OS/2'

		);

		if (!$_SERVER['HTTP_USER_AGENT']) return false;

		foreach($OSstrings as $osname => $matcher)
		{
			if (preg_match($matcher, $_SERVER['HTTP_USER_AGENT'])) break;
		}
		return $osname;

	}

    public static function clientOSFamily()
    {
        $OSstrings = array
        (
            'Windows' => '(Windows)|(Win)|(WinNT)',
            'OpenBSD' => 'OpenBSD',
            'FreeBSD' => 'FreeBSD',
            'SunOS' => 'SunOS',
            'Linux' => 'Linux',
            'Mac' => '(Mac_PowerPC)|(Macintosh)|(OS X)',
            'QNX' => 'QNX',
            'BeOS' => 'BeOS',
            'OS/2' => 'OS/2'
        );

        if (!$_SERVER['HTTP_USER_AGENT']) return false;

        foreach($OSstrings as $osname => $matcher)
        {
            if (eregi($matcher, $_SERVER['HTTP_USER_AGENT'])) break;
        }
        return $osname;

    }

}

?>
