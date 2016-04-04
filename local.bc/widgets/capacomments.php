<?php

function widget_capacomments($options)
{

    extract($options);
    
    $comment_html = "
        
        <div class='problemlist comBb'>

            <div class='infoblock'>
                <div class='infotitle'>
                    <p>Комментарии</p>
                </div>
    
    ";

    foreach ($comments as $comment) {

        if ( $comment['docpath'] != $capa->urn ) continue;
        
        $created = date('d.m.Y', $comment['created']);
        
        $comment_html .= "
            
            <div class='commentblock'>
                <div class='comment'>
                    <p>{$comment['content']}</p>
                </div>
                <div class='infocomment'>
                    <p class='posttime'>{$created}</p>
                    <div class='commentauthor'>
                        <p>{$comment['autor_id']['name']} - {$comment['autor_id']['id']}</p>
                        <p>Менеджер по роботе с клиентами</p>
                    </div>
                </div>
                <div class='addCommentButtonreply'>
                    <input type='submit' value='' class='reply_btn' data-comment='{$comment['id']}' data-document='{$capa->id}'/>
                </div>
            </div>
        ";

        foreach ($comment['reply'] as $reply) {
            
            $reply_created = date('d.m.Y', $reply['created']);
            
            $comment_html .= "
                
                <div class='commentblock commentreply firstreply'>
                    <div class='comment'>
                        <p>{$reply['content']}</p>
                    </div>
                    <div class='infocomment'>
                        <p class='posttime'>{$reply_created}</p>
                        <div class='commentauthor'>
                            <p>{$reply['autor_id']['name']} - {$reply['autor_id']['id']}</p>
                            <p>Менеджер по роботе с клиентами</p>
                        </div>
                    </div>
                </div>
            
            ";
            
        }
    }
    
    $comment_html .= "
    
            </div>
            <div class='addcomment'>
                <form action='/comments/AddNewComment' data-managedform='yes' data-onsuccess='addComment' data-onerror='capaCreateError'>

                    <input type='hidden' data-selector='doc_id' value='{$capa->id}'>
                    <input type='hidden' data-selector='doc_path' value='{$capa->urn}'>
                    <input type='hidden' data-selector='user' value='{$user->id}'>
                    <textarea placeholder='Введите свой комментарий' data-selector='content'></textarea>
                    <div class='addCommentButton'>
                        <input type='submit' value='' />
                    </div>
                </form>
            </div>

        </div>
    ";
    
    echo $comment_html;
}

?>