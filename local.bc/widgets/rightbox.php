<?php

function widget_rightbox($options)
{
    /*
    title --- назваание блока
    user ---  масив юзеров с ключами
                                    name - имя
                                    post - должность


    attr --- ппараметр создающий снизу блок с кнопкой добавить (является параметром data-openwindow)
    button ---          имя кнопки


    link ---  масив ссылок с ключами
                                    href - url
                                    link - текст url


    */


    extract($options);
    $class = 'userinfopart';
    $but = '';
    $users = '';
    if($user){
        $class = 'userinfopart';
        foreach($user as $u) {
            $n = $u['name'];
            $p = $u['post'];
            $users .= "<div class='user'>
                        <p class='name' >{$n}</p >
                        <p class='post' >{$p}</p >
                    </div >";
        }
    }

    $links = '';
    if($link && !$user){
        $class = 'filelistpart';
        foreach($link as $u) {
            $h = $u['href'];
            $l = $u['link'];
            $links .= "<a href='{$h}'>{$l}</a>";
        }
    }

    if($attr){
        $but = "
                <div class='TM BLK btnblock'>
                    <div class='FL'></div>
                    <div class='FR add CP'>
                        <p><a data-openwindow='{$attr}' data-richtype='{$richtype}' data-call='{$class}_bridge' data-windowcontentrenderer='UIGeneralSelectWindow' href = '#' >{$button}</a ></p>
                    </div>
                </div>
            ";
    }

    echo "
                    <div class='{$class}' >
                        <div class='box' >
                            <label >{$title}:</label >
                            <div class='list'>
                                {$users}
                                {$links}
                            </div>
                            {$but}
                        </div >
                    </div >
        ";


}
