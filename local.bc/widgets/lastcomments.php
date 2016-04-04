<?php

function widget_lastcomments($options)
{

    extract($options);


    $html = <<<HTML
<div id="lastCommentPopup" class="fixed-overlay fixed-overlay__modal" style="display: none">
    <div class="modal">
        <div class="modal_container">
            <div class="toasttop">
                <img src="/img/close.png" style="" id="commentPopupClose">
            </div>
            <div class="title">
                CANCEL COMMENT
            </div>
            <div class="cancelComment">
                <div class="when">
                    <p></p>
                </div>
                <div class="whouser">
                    <p></p>
                </div>
                <div class="post">
                    <p></p>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;



    echo $html;

}