<?php

function widget_capareplycomment($options)
{

    extract($options);
    
    echo "
        
    <!--popup-->
    <div id='tosatelement' style='display: none;'>
        <div class='toast animated' style='display: none; opacity: 0; position: relative;'>
            <div>
                <img id='segmentClose' style='' src='/img/close.png'>
                <h4>Добавление ответа на комментарий</h4>
            </div>
            <form style='width: 300px; margin: 0 auto; text-align: center;' action='/comments/AddNewReply' data-managedform='yes' data-onsuccess='reply_comment'  data-onerror='capaCreateError' >
                <label class='IBLK FS15 FL' style='line-height: 30px; color: #000000;'>Текст ответа</label>
                <input type='hidden' required='required' data-selector='parent_id' id='reply_parent_id' value=''>
                <input type='hidden' required='required' data-selector='document' id='reply_document' value=''>
                <textarea class='' required='required' id='description' data-selector='content' placeholder='Введите текст ответа'></textarea>
                <button style='margin: 45px 0 0 0' class='IBLK w200 GBTN' type='submit'>Отправить</button>
            </form>
            <span></span>
        </div>
    </div>

    <script>

        newSegm();

        function toast(x,y){
            var toast = document.getElementsByClassName('toast')[0];
            toast.style.display = 'block';
            toast.querySelectorAll('span')[0].innerHTML = x;
            setTimeout(function(){ toast.style.opacity = '1'},100);
        }

        function newSegm() {
            var segm = document.getElementsByClassName('reply_btn');
            var segmTop = document.getElementById('tosatelement');
            var x = '';

            for ( var i = 0; i < segm.length; i++ ) {

                Event.add(segm[i], 'click', function (e) {
                    e.preventDefault();

                    var parent_id = this.getAttribute('data-comment');
                    var document_ = this.getAttribute('data-document');

                    document.getElementById('reply_parent_id').value = parent_id;
                    document.getElementById('reply_document').value = document_;

                    segmTop.style.display = 'block';
                    toast(x);
                });
            }

            var segmentClose = document.getElementById('segmentClose');
            Event.add(segmentClose, 'click', function (e) {
                segmTop.style.display = 'none';
            });
        }

    </script>
    
    ";

}

?>