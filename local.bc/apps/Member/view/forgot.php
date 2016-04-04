<?
extract($this->context);
?>

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

    <div class="content">

        <!-- BEGIN: Login Form. -->
        <div class="form request-reset-form">

            <header class="circle-logo invision">
                <span></span>
            </header>

            <div class="form-wrap">

                <form action="/User/Forgot" method="post" data-managedform="yes" data-onsuccess="userForgot" data-onerror="HelloError" data-legacycontrol="yes" novalidate>

                    <p class="login-title">Введите свой адрес электронной почты.</p>

                    <div class="input-layout">
                        <input type="text" required class="cleanup validate-empty " name="email" id="email" value="" data-selector="email">
                        <span class="highlight"></span>
                        <span class="bar"></span>
                        <label>Email</label>

                        <div class="alert alert-error alert-dynamic" id="emailError" style="display:none; visibility: hidden; opacity: 0; transition: all 0.5s ease 0s;">
                            <strong>Ууупс!</strong>: Такой e-mail не зарегестрирован в системе.
                        </div>

                        <div class="alert alert-error alert-email">
                            Ууупс! Емейл обязателен.
                        </div>
                    </div>

                    <footer>
                        <button type="submit" class="primary button large">Выслать новый пароль</button>
                    </footer>

                </form>

            </div> <!-- form-wrap -->

        </div>
        <!-- END: Login Form. -->


        <div class="bottomlinks">

            <a href="/member/login" class="gray">Вернуться к Входу</a>

        </div>

        <script type="text/javascript">

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
                $(this).removeClass("error");
            });

        </script>

    </div>

</div>