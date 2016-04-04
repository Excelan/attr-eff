<?php

function widget_comments($options)
{
    /*

    Обязательно:
    $urn - урн документа к которому пренадлежит комментарий
    $level
            FirstLevel - доступны все кнопки
            SecondLevel - пока нету роли
            ThirdLevel - пока нету роли
    _________________________________________________________________
    Опционально:
                $field - поле документа к которому пренадлежит комментарий
                $idCapa - id div для комментов капы

    */


    extract($options);

    if($level == 'FirstLevel') {
        $NewCommentGlobal = <<<HTML
    <div class="titleAddNewComments" style="clear: both; display: inline-block; margin-top: 100px; padding: 0 20px;">Добавить новый комментарий</div>
    <div class="buttonAddNewComment">
        <form data-onerror="HelloError" data-onsuccess="addComment" data-managedform="yes" action="/Comments/AddNewComment">
            <textarea data-selector="content" placeholder="Введите свой комментарий"></textarea>
            <input data-selector="urn" type="hidden" value="{$urn}">
            <input data-selector="userid" type="hidden" value="{$user}">
            <input data-selector="level" type="hidden" value="{$level}">
            <input data-selector="idCapa" type="hidden" value="{$idCapa}">
            <div class="addCommentButton">
                <input type="submit" value="">
            </div>
        </form>
    </div>
HTML;
    }else{
        $NewCommentGlobal = '';
    }

    if($urn) {
        $m = new Message();
        $m->action = 'load';
        $m->urn = "urn:Communication:Comment:Level2withEditingSuggestion";
        $m->order = array('created' => 'ASC');
        $m->document = $urn;
        if($field)$m->docpath = $field;
        $comments = $m->deliver();

        $GlobArr = array();
        foreach($comments as $c){
            array_push($GlobArr,$c);
        }

        $arrComment = '';
        foreach($GlobArr as $comment) {

            $resultComments = '';
            if(!$comment->replyto){
                $oneComment = '';
                //$commentDate = date('d.m.Y', $comment->created);
                $commentTime = date('H:s', $comment->created);
                $commentAuthor = $comment->autor->title;
                $commentPost = $comment->autor->ManagementPostIndividual->title;
                $commentText = $comment->content;



                if($level == 'FirstLevel') {
                    $buttonAddNewCommentHide = <<<HTML
    <div class="buttonAddNewComment NewCommentCreate" style="display: none">
        <form data-onerror="HelloError" data-onsuccess="addComment" data-managedform="yes" action="/Comments/AddNewComment">
            <textarea data-selector="content" placeholder="Введите свой комментарий"></textarea>
            <input data-selector="urn" type="hidden" value="{$urn}">
            <input data-selector="replyto" type="hidden" value="{$comment->id}">
            <input data-selector="userid" type="hidden" value="{$user}">
            <input data-selector="level" type="hidden" value="{$level}">
            <input data-selector="idCapa" type="hidden" value="{$idCapa}">
            <div class="addCommentButton">
                <input type="submit" value="">
            </div>
        </form>
    </div>
HTML;

$counted = '';

if($comment->appliedstatus == 'new') $counted = <<<HTML
        <div class="apply IBLK FR">
            <form data-onerror="HelloError" data-onsuccess="changeCommentStatus" data-managedform="yes" action="/Comments/ChangeCommentStatus">
                <input type="submit" value="Учесть" class="inputclear">
                <input data-selector="comment" type="hidden" value="{$comment->urn}">
                <input data-selector="urn" type="hidden" value="{$urn}">
                <input data-selector="status" type="hidden" value="applied">
                <input data-selector="userid" type="hidden" value="{$user}">
                <input data-selector="level" type="hidden" value="{$level}">
                <input data-selector="idCapa" type="hidden" value="{$idCapa}">
            </form>
        </div>
HTML;
else if($comment->appliedstatus == 'applied') $counted = <<<HTML
        <div class="counted IBLK" >
            <p class="IBLK">Комментарий учтен</p>
        </div>
HTML;
else if($comment->appliedstatus == 'done') $counted = <<<HTML
        <div class="сhanges IBLK" >
            <p class="IBLK">Изменения приняты</p>
        </div>
HTML;
else $counted = <<<HTML
        <div class="IBLK">
            <p class="IBLK">Не известный статус</p>
        </div>
HTML;

$buttonReplyTake = <<<HTML
    <div class="buttonReplyTake" style="display: none">
        <div class="reply IBLK">
            <input type="submit" value="Ответить" class="inputclear buttonReply">
        </div>
        {$counted}
    </div>
HTML;
                }else{
                    $buttonAddNewCommentHide = '';
                    $buttonReplyTake = '';
                }

                if($comment->cancel == true){
                    $oneComment = <<<HTML
                    <div class="rightCommentBlock">
                    <div class="when">
                        <p><span></span></p>
                    </div>
                    <div class="onecomment">
                        <div class="author">{$commentAuthor}</div>
                        <div class="authorpost">{$commentPost}<span>{$commentTime}</span></div>
                        <div class="textCancelcomment textcommentFirstLevel">
                            <p>{$commentText}</p>
                        </div>
                    </div>
                    {$buttonReplyTake}
                    {$buttonAddNewCommentHide}
HTML;

                }else {
                    $oneComment = <<<HTML
                    <div class="rightCommentBlock">
                    <div class="when">
                        <p><span></span></p>
                    </div>
                    <div class="onecomment">
                        <div class="author">{$commentAuthor}</div>
                        <div class="authorpost">{$commentPost}<span>{$commentTime}</span></div>
                        <div class="textcomment textcommentFirstLevel">
                            <p>{$commentText}</p>
                        </div>
                    </div>
                    {$buttonReplyTake}
                    {$buttonAddNewCommentHide}
HTML;
                }


                $resultReplyComments = '';
                foreach ($GlobArr as $commentR) {
                    $commentreply = '';
                    if ($commentR->replyto == $comment->id) {

                        //$commentRDate = date('d.m.Y', $commentR->created);
                        $commentRTime = date('H:s', $commentR->created);
                        $commentRAuthor = $commentR->autor->title;
                        $commentRPost = $commentR->autor->ManagementPostIndividual->title;
                        $commentRText = $commentR->content;


                        $commentreply = <<<HTML
                            <div class="commentreply">
                                <div class="author">{$commentRAuthor}</div>
                                <div class="authorpost">{$commentRPost}<span>{$commentRTime}</span></div>
                                <div class="textcomment">
                                    <p>{$commentRText}</p>
                                </div>
                            </div>
HTML;

                    }
                    $resultReplyComments .= $commentreply;
                }

                $resultComments .= $oneComment . $resultReplyComments . "</div>";

            }
            $arrComment .= $resultComments;
        }

        $arrComment .= $NewCommentGlobal."<script>displayCommentButton();</script>";

    }



    if($readcomments == 'FirstLevel' || $level == 'FirstLevel')
    echo "
           {$arrComment}
        ";
    else
        echo "<div class='rightCommentBlock'>
                    <div class='onecomment'>
                        <p style='text-align: center;'><span>Нет прав для просмотра</span></p>
                    </div>
              </div>";


}