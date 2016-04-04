<?php $r = "11" ?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="ru">
<head>
    <meta charset="utf-8">
    <title><title /></title>
    <!-- <link rel="stylesheet" href="/goldcut/assets/css/admin.css"> -->

    <!--  traceur-compiler for load <module es6, traceur-runtime for run precomplied -->



    <script type="text/javascript" src="/js/jquery-1.8.2.min.js"></script>
    <!-- <script src="http://code.jquery.com/jquery-latest.js"></script> -->
    <script type="text/javascript" src="/js/jquery-ui.js"></script>
    <script type="text/javascript" src="/js/browser.js"></script>
    <link rel="stylesheet" href="/css/jquery-ui.css" type="text/css" media="screen" charset="utf-8">

    <script type="text/javascript" src="/js/settings.js?3"></script>
    <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
    <!-- <script type="text/javascript" src="/lib/js/tinymce/tinymce.min.js"></script> -->
    <!-- <script type="text/javascript" src="/goldcut/js/tinymce.js?1"></script> -->




    <script type="text/javascript" src="/js/jquery.timepicker.js"></script>
    <link rel="stylesheet" type="text/css" href="/css/jquery.timepicker.css" />







    <script src="/lib/js/when/when.js"></script>


    <!-- <script src="/lib/js/jsondiff.js"></script> -->
    <script src="/goldcut/js/gcdom.js"></script>
    <script src="/goldcut/js/stacktrace.js"></script>
    <script src="/goldcut/js/gcoo.js"></script>
    <script src="/goldcut/js/gcui.js?<?=$r?>"></script>
    <script src="/goldcut/js/gcfileupload.js"></script>
    <script src="/goldcut/js/gcutil.js"></script>
    <script src="/goldcut/js/goldcut.js"></script>
    <script src="/goldcut/js/ui/UIGeneralSelectWindow.js"></script>
    <script src="/goldcut/js/gcmanagedforms.js?<?=$r?>"></script>
    <script src="/goldcut/js/managedformbuilder.js?<?=$r?>"></script>

    <script src="/lib/js/node_modules/systemjs/dist/system.js" type="text/javascript"></script>
    <script src="/lib/js/bower_components/textarea-autogrow/textarea-autogrow.js" type="text/javascript"></script>

    <link rel="stylesheet" href="/goldcut/assets/css/gcgrid.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="/goldcut/assets/css/managedform.css" type="text/css" media="screen" charset="utf-8">

    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,500,400italic,300italic,500italic,700,700italic,900&subset=latin,cyrillic,cyrillic-ext' rel='stylesheet' type='text/css'>

    <script src="/js/gcmevents.js?<?=$r?>" type="text/javascript"></script>
    <script src="/js/startprocesswindow.js" type="text/javascript"></script>
    <script src="/js/attestation.js" type="text/javascript"></script>

    <script src="/goldcut/js/clientofcloudcontrol.js"></script>

    <link rel="stylesheet" href="/css/gcm.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="/css/style.css" type="text/css" media="screen" charset="utf-8">
    <script>
        GC.ONLOAD.push(function (e) {
            setTimeout(function() {
                //id('submitInternal').click();
                //tinyMCE.init(tinyOptions);


            }, 500);
        });

        /*
        GC.CALLBACKS['onmoveproductsetnumbers'] = function ()
        {
            var val = parseInt(this.getAttribute('data-avail'));
            console.log("CALLBACK", val);
        }
        */

    </script>
    <script src="/js/site.js" type="text/javascript"></script>
</head>

<body class="GCM withdommanager <namespace />">
<?php
extract($this->context);


if ($this->user->id) {
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:Actor:User:System';
    $m->id = $this->user->id;
    $tu = $m->deliver();
}

if ($tu) {
    $m = new Message();
    $m->action = 'load';
    $m->urn = 'urn:People:Employee:Internal';
    $m->ActorUserSystem = (string)$tu->urn;
    $emp = $m->deliver();

    echo $tu->name.' --- '.$tu->email.' --- '.$emp->ManagementPostIndividual->title;
}


$m = new Message();
$m->action = 'load';
$m->urn = 'urn:People:Employee:Internal';
$m->ActorUserSystem = (string)$tu->urn;
$emp = $m->deliver();



?>

<input type="hidden" id="initiator" value="<?php if (count($this->managementrole)) {
    echo $this->managementrole->urn;
} ?>">
<input type="hidden" id="employee" value="<?php if (count($this->employee)) {
    echo $this->employee->urn;
} ?>">
<?php
echo $HTML;
?>

<div id="overlay" class='overlay hide animated'></div>
<a style="display: block; text-align:right;margin-top: 100px; margin-right: 20px;  font-size:10px; color: #ccc; text-decoration: none;" href="/member/logout">выход</a>

<script>

        if ( BrowserDetect.browser == "Firefox" || BrowserDetect.browser == "Safari") {
        $(document).ready(function () {

            var time = $('#eventTime');
            if(time){
                $('#eventTime').timepicker({'timeFormat': 'H:i'});
            }

            $(document).on('mouseenter', "input[type='date']", function (e) {
                $("input[type='date']").datepicker({
                    closeText: 'Закрыть',
                    prevText: '&#x3c;Пред',
                    nextText: 'След&#x3e;',
                    currentText: 'Сегодня',
                    monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                    monthNamesShort: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                    dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
                    dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
                    dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                    weekHeader: 'Нед',
                    dateFormat: 'dd.mm.yy',
                    firstDay: 1,
                    //isRTL: false,
                    showMonthAfterYear: false,
                    yearSuffix: ''
                });
            })
        });
    }



</script>

<script type="text/javascript" src="/js/lightbox.js"></script>
<link rel="stylesheet" href="/css/lightbox.css" type="text/css">

</body>
</html>
