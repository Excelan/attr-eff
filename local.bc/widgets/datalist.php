<?php

function widget_datalist($options)
{
    extract($options);

    $data_multiplestruct = $struct ? "data-multiplestruct='{$struct}'" : "";
    $data_struct = $struct ? "data-struct='{$struct}' data-array='item'" : "";
    $struct_name = $struct ? "{$struct}-" : "";

    $items_list_html = "";
    foreach ($data_items as $item) {

        $item_html = "";
        if ( $item_type == 'link' ) {

            $class = "list";

            $item_html .= "
                <a target='_blank' href='{$item['href']}'>{$item['source']}</a>                                                                            
            ";

        } else if ( $item_type == 'tailhead' ) {

            $item_html .= "
                <p class='name'>{$item['head']}</p>
                <p class='post'>{$item['tail']}</p>
            ";

        } else {

            $item_html .= "
                <p class='name'>{$item['head']}</p>
                <p class='post'>{$item['tail']}</p>
            ";
        }


        $input_html = $item['name'] ? "<input type='hidden' data-selector='{$struct_name}{$item['name']}' value='{$item['value']}'>" : "";

        $items_list_html .= "
        <div class='{$class}' {$data_struct}>
            {$item_html}
            {$input_html}
        </div>";
    }

    $btn_html = "";
    if ( $btn ) {
        $btn_html .= "
            <div class='TM BLK btnblock' id='{$btn}'>
                <div class='FL'></div>
                <div class='FR add CP'>
                    <p><a href='#'>{$btn_title}</a></p>
                </div>
            </div>
        ";
    }

    echo "

            <div class='box' {$data_multiplestruct}>
                <label>{$title}:</label>
                <div id='{$list_id}'>

                    <div class='user'>
                        {$items_list_html}
                    </div>

                </div>

                {$btn_html}

            </div>
11111
    ";


}

?>
