<?php

namespace Selector;

class TestSelect extends \Gate
{
    public function gate()
    {
        $data = $this->data;

        return [
            'options' => [
            	['value' => 'V1','title' => 'T1'],
            	['value' => 'V2','title' => 'T2'],
            ],
        ];
    }
}

?>
