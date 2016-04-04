<?php

function widget_inforow($options)
{

    extract($options);

    echo "
            <div class='stateinfo'>
                <p>
                    {$name}
                    <span>{$value}</span>
                </p>
            </div>
    ";

}

?>
