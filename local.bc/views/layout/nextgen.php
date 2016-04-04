<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title><title /></title>
    <!--  traceur-compiler for load <module es6, traceur-runtime for run precomplied -->
    <script src="/js/site.js" type="text/javascript"></script>
    <script src="/lib/js/when/when.js"></script>
    <script src="/goldcut/js/gcdom.js"></script>
    <script src="/goldcut/js/gcoo.js"></script>
    <script src="/goldcut/js/gcui.js"></script>
    <script src="/goldcut/js/ui/UIGeneralSelectWindow.js"></script>
    <script src="/goldcut/js/gcmanagedforms.js"></script>
    <script src="/goldcut/js/managedformbuilder.js"></script>

    <script src="/lib/js/node_modules/systemjs/dist/system.js" type="text/javascript"></script>
    <script src="/lib/js/bower_components/textarea-autogrow/textarea-autogrow.js" type="text/javascript"></script>

    <link rel="stylesheet" href="/goldcut/assets/css/gcgrid.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="/goldcut/assets/css/managedform.css" type="text/css" media="screen" charset="utf-8">

    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,500,400italic,300italic,500italic,700,700italic,900&subset=latin,cyrillic,cyrillic-ext' rel='stylesheet' type='text/css'>

    <link rel="stylesheet" href="/css/gcm.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="/css/style.css" type="text/css" media="screen" charset="utf-8">

    <script>

        GC.CALLBACKS['onmoveproductsetnumbers'] = function ()
        {
            var val = parseInt(this.getAttribute('data-avail'));
            console.log("CALLBACK", val);
        }

        function SimpleDynamicWindow(opts) {
            console.log('SimpleDynamicWindow', opts);
            var html = this.buildHTML()
        }
        SimpleDynamicWindow.prototype.buildHTML = function()
        {
            var df = document.createDocumentFragment();
            var main = document.createElement('main');
            main.classList.add('reddebugbox100');
            df.appendChild(main);
            this.html = df;
        }
        SimpleDynamicWindow.prototype.getDom = function()
        {
            return this.html;
        }

        function onclickdynwin(callerdata, windowdom)
        {
            //selectchange();
            console.log('onclickdynwin');
            //console.log(this); // caller a link
            console.log(callerdata); // caller a link data-*
            //console.log(callerdata.callparam);
            console.log(windowdom); // all rendered modal window
        }



    </script>

    <style>
        body { font-family: sans-serif; font-size: 80%; }
        #managedform {
            /*border: 1px solid #ccc;*/
            margin: 0 auto;
        }
    </style>
</head>
<body class="GCM withdommanager">


<?
echo $HTML;
?>

<div id="overlay"></div>
</body>
</html>
