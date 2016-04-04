<?php

class DefinitionPrototypeSystemPlugin extends RowPlugin
{

    public function adminview()
    {
        return "{$this->ROW->indomain}:{$this->ROW->ofclass}:{$this->ROW->oftype}";
    }

    function check()
    {
      $ofclass = preg_replace('/[^\x20-\x7E]/', '', $this->ROW->ofclass);
      if ($ofclass != $this->ROW->ofclass) $css = "color: red;";
      $oftype = preg_replace('/[^\x20-\x7E]/', '', $this->ROW->oftype);
      if ($oftype != $this->ROW->oftype) $css = "color: red;";
      if ($css) return "<span style='$css'>".$ofclass .':'. $oftype.'</span>';
    }

}

?>