<?php $r = "5" ?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="ru">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024"/>
    <meta />
    <title><title /></title>

    <script src="/js/site.js" type="text/javascript"></script>

    <link rel="stylesheet" href="/goldcut/assets/css/gcgrid.css" type="text/css" media="screen" charset="utf-8">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,500,700,900&subset=latin,cyrillic-ext,cyrillic' rel='stylesheet' type='text/css'>

    <link rel="stylesheet" href="/css/gcm.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="/css/style.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="/css/bcstyle.css" type="text/css" media="screen" charset="utf-8">

    <script type="text/javascript" src="/js/settings.js?3"></script>
    <script type="text/javascript" src="/lib/js/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="/goldcut/js/tinymce.js?3"></script>


    <script type="text/javascript" src="/goldcut/js/stacktrace.js"></script>

    <script type="text/javascript" src="/lib/js/when/when.js?<?=$r?>"></script>
    <script type="text/javascript" src="/goldcut/js/gcdom.js?<?=$r?>"></script>
    <script type="text/javascript" src="/goldcut/js/gcmanagedforms.js?<?=$r?>"></script>
    <script type="text/javascript" src="/goldcut/js/gcui.js?<?=$r?>"></script>
    <script type="text/javascript" src="/goldcut/js/gctemplate.js?<?=$r?>"></script>
    <script type="text/javascript" src="/lib/js/objectpath.js"></script>

    <script src="/js/gcmevents.js?<?=$r?>" type="text/javascript"></script>


</head>

<body class="GCM withdommanager <namespace />">

<?
echo $HTML;
?>

<div id="overlay" class='overlay hide animated'></div>
<a style="display: block; text-align:right;margin-top: 100px; margin-right: 20px;  font-size:10px; color: #ccc; text-decoration: none;" href="/member/logout">выход</a>
</body>
</html>
