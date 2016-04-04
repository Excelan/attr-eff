<?php

class File_Streamer
{
	private $fileName;
	private $contentLength;
	private $path;

	public function __construct()
	{
        if (array_key_exists('HTTP_X_FILE_NAME', $_SERVER) && array_key_exists('CONTENT_LENGTH', $_SERVER)) {
            $filename = $_SERVER['HTTP_X_FILE_NAME'];
            $fe = explode(".", $filename);
            $c = count($fe)-1;
            $ext = $fe[$c];
            $lenneg = -1 * strlen($ext);
            $this->fileName = substr(Security::filterString09AZaz_dahed($filename), 0, $lenneg);
            $this->fileName .=  '-'.rand(1000000,9000000);
            $this->fileName = $this->fileName .'.'. $ext;
            $this->contentLength = $_SERVER['CONTENT_LENGTH'];
        }
        else
            throw new Exception("Error retrieving headers");
	}

    public function setDestination($p)
    {
    	$this->path = $p;
			mkdir($this->path, 0777, true);
    }

    public function receive()
    {
        if (!$this->contentLength > 0) {
            throw new Exception('No file uploaded!');
        }

        file_put_contents(
            $this->path . $this->fileName,
            file_get_contents("php://input")
        );

        return $this->path.$this->fileName;
    }
}