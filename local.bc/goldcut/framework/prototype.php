<?php
class Prototype {

    private $inDomain;
    private $ofClass;
    private $ofType;

    public function __construct($str)
    {
        if (!$str) throw new Exception("NULL PROTOTYPE");
        $a = explode(':', $str);
        $this->inDomain = $a[0];
        $this->ofClass = $a[1];
        $this->ofType = $a[2];
    }

    public function __toString()
    {
        return "{$this->inDomain}:{$this->ofClass}:{$this->ofType}";
    }

    public function toTableName()
    {
        return "{$this->inDomain}_{$this->ofClass}_{$this->ofType}";
    }

    public function toColumnName()
    {
        return "{$this->inDomain}{$this->ofClass}{$this->ofType}";
    }

    /**
     * @return mixed
     */
    public function getInDomain()
    {
        return $this->inDomain;
    }

    /**
     * @param mixed $inDomain
     */
    public function setInDomain($inDomain)
    {
        $this->inDomain = $inDomain;
    }

    /**
     * @return mixed
     */
    public function getOfClass()
    {
        return $this->ofClass;
    }

    /**
     * @param mixed $ofClass
     */
    public function setOfClass($ofClass)
    {
        $this->ofClass = $ofClass;
    }

    /**
     * @return mixed
     */
    public function getOfType()
    {
        return $this->ofType;
    }

    /**
     * @param mixed $ofType
     */
    public function setOfType($ofType)
    {
        $this->ofType = $ofType;
    }



}
?>
