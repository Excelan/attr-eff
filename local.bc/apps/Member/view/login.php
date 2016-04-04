<?php
extract($this->context);
//println('---------'.$this->user->id);
?>

<body>


<img src="/img/Industrial_warehouse.png" id="bg" alt="">

<div class="animated" id="notification">
    <div class="content">
        <p class="nottext">Ошибка загрузки</p>
        <p class="notimg">
            <img src="/img/closebig.png">
        </p>
    </div>
</div>

<div id="overlayto" class="no-overlay-img">

    <div class="alert alert-success" style="display: none;">
        <strong>Sweet</strong>: Your password reset email has been sent.
    </div>
    <div class="content">
        <!-- BEGIN: Login Form. -->
        <div class="form login-form">

            <header class="circle-logo invision">
                <span></span>
            </header>

            <div class="form-wrap clearfix">

                <p class="login-title">Войти в BIOCON QMS</p>
                <form action="/User/Login" method="post" data-managedform="yes" data-onsuccess="userLogin" data-onerror="userLoginError" novalidate>

                    <div class="input-layout">
                        <input type="text" required="required" class="cleanup validate-empty " name="email" id="email" value="" data-selector="email"/>
                        <span class="highlight"></span>
                        <span class="bar"></span>
                        <label>Email</label>

                        <div class="alert alert-error alert-email">
                            Ууупс! Емейл обязателен.
                        </div>
                    </div>

                    <div class="input-layout">
                        <input type="password" required="required" class="validate-empty" name="password" id="password" value="" data-selector="password"/>
                        <span class="highlight"></span>
                        <span class="bar"></span>
                        <label>Password</label>
                        <div class="alert alert-error alert-password">
                            Ууупс! Пароль обязателен.
                        </div>
                    </div>

<!--                    <div id="memberNotification" class="animated">-->
<!--                            Авторизовано-->
<!--                    </div>-->
                    <footer>
                        <button type="submit" class="primary button">Войти</button>
                    </footer>

                </form>

            </div> <!-- form-wrap -->

        </div>
        <!-- END: Login Form. -->

        <div class="bottomlinks clearfix">

            <a href="/member/forgot" id="forgotPassword">Забыли пароль?</a>

            <span class="getStarted"><span class="gray">Еще не зарегетрированы?</span> <a href="/member/register">Регистрация</a></span>

        </div>

    </div>

</div>
<script type="text/javascript">

    (function() {

        var form = $( "form" );


        var currentAction = form.attr( "action" );

        form.attr( "action", ( currentAction + location.hash ) );


        $( "#keepMeLoggedIn" ).uniform();


        // check if there is a redirect supposed to happen
        if( window.location.hash ){
            $('#redirHash').val( window.location.hash.substr(1) );
        }


        // To help clean up the cache, we're going to flush the localStorage if the user is
        // explicitly logging-in. This way, if the user has been logged out (maybe by flushing
        // cookies or timeout), then we'll make sure they don't see dirty data.
        try {

            window.localStorage.clear();

        } catch ( error ) {

            // Silently fail if localStorage is not supported.

        }

    })();

    // Fade out success alerts if visible

    if ($(".alert-success").length) {
        $('.alert-success').delay(5000).fadeOut('slow');
    }

    // Check for empty fields

    $("form").submit(function() {

        var inputs = $(this).find('.validate-empty');
        var areFieldsValid = true;

        inputs.each(function (el) {

            if ($(this).val().length === 0) {
                $(".alert-" + $(this).attr('id')).show();
                $(this).addClass("error");
                areFieldsValid = false;
            }
        });

        return areFieldsValid;

        if (areFieldsValid) {
            $(this).preventDefault();
        }
    });

    $("input.cleanup").blur(function() {
        var value = $.trim( $(this).val() );
        $(this).val( value );
    });

    // Hide errors on focus

    $(".validate-empty").focus(function () {
        $(".alert-" + $(this).attr('id')).hide();
        $(".alert-dynamic").hide();
        $("#memberNotification").css({"visibility":"hidden", "opacity":"0"});

        $(this).removeClass("error");
    });

    $(".getStarted a").on("click", function(ev) {
        ev.preventDefault();

        if (window.analytics) window.analytics.track("Account.SignUpStarted");

        window.location.href = $(this).attr("href");
    });


</script>



</body>