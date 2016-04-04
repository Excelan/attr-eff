<?php $r = "5" ?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="ru">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1024"/>
    <meta />
    <title><title /></title>



    <!-- Custom webfonts -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=latin,cyrillic,latin-ext,cyrillic-ext' rel='stylesheet' type='text/css'>

    <!-- Custom style -->
    <link rel="stylesheet" href="/css/bootstrap.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="/css/login.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="/goldcut/assets/css/gcgrid.css" type="text/css" media="screen" charset="utf-8">

    <!-- Js Library -->
    <script src="/js/jquery-1.8.2.min.js?<?=$r?>" type="text/javascript"></script>
    <script src="/js/bootstrap.min.js?<?=$r?>" type="text/javascript"></script>
    <script src="/js/jquery.uniform.min.js?<?=$r?>" type="text/javascript"></script>
    <script src="/js/jquery.placeholder.min.js?<?=$r?>" type="text/javascript"></script>


    <!-- gc managed forms-->
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
