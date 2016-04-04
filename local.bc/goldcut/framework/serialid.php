<?php
class SerialID
{

    private $id = 0;
    private static $instance;

    private function __construct() {}

    public static function manager()
    {
        if (!self::$instance) { self::$instance = new SerialID(); } return self::$instance;
    }

    public function nextmanaged()
    {
        return ++$this->id;
    }

    public static function next()
    {
        if (!self::$instance) { self::$instance = new SerialID(); } return self::$instance->nextmanaged();
    }

}
?>