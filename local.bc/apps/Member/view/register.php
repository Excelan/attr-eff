<?
extract($this->context);
?>

<body>

<img src="/img/Industrial_warehouse.png" id="bg" alt="">


<div id="overlayto" class="no-overlay-img">
    <div class="content">

        <!-- BEGIN: Signup Form. -->
        <div class="form signup-form">

            <header class="circle-logo invision">
                <span></span>
            </header>

            <form action="/User/Register" data-managedform="yes" data-onsuccess="userRegister"  data-onerror="capaCreateError" method="post" novalidate>

                <input type="hidden" name="external" data-selector="external" value="1">

                <p class="login-title">Регистрируйтесь
                    <span>УЖЕ СЕЙЧАС</span>
                    <span class="small">СИСТЕМА БИОКОН</span>
                </p>


                <div class="input-layout">
                    <input type="text" class="cleanup validate-empty " name="email" id="email" value="" data-selector="email" required="required"/>
                    <span class="highlight"></span>
                    <span class="bar"></span>
                    <label>Введите email</label>


                    <div class="alert alert-error alert-email">
                        Ууупс! Емейл обязателен.
                    </div>
                </div>


                <div class="input-layout">
                    <input type="password" class="validate-empty" name="password" id="password" value="" data-selector="providedpassword" required="required"/>
                    <span class="highlight"></span>
                    <span class="bar"></span>
                    <label>Введите пароль</label>
                    <div class="alert alert-error alert-password">
                        Ууупс! Пароль обязателен.
                    </div>
                </div>

                <div class="input-layout">
                    <input type="password" class="validate-empty" name="password" id="password" value="" data-selector="providedpasswordcopy" required="required"/>
                    <span class="highlight"></span>
                    <span class="bar"></span>
                    <label>Повторите пароль</label>
                    <div class="alert alert-error alert-password">
                        Ууупс! Пароль обязателен.
                    </div>
                </div>


                <footer class="clearfix">

                    <button type="submit" class="primary button" tabindex="4">
                        Зарегистрироваться
                    </button>



                    <p class="legalNotice">
                        Нажимая на  "Зарегистрироваться" я соглашаюсь с условиями использования
                        <a href="http://www.invisionapp.com/terms_of_service" target="_blank">Условия использования</a>.
                    </p>

                </footer>

            </form>

        </div>
        <!-- END: Signup Form. -->


        <div class="bottomlinks">
            <span class="gray">Уже имеете аккаунт?</span> <a href="/member/login">Вход!</a>
        </div>



        <script type="text/javascript">
            $(function(){
                // Close modal link just got history.back()
                $("#closeModal").click(function( event ){

                    event.preventDefault();
                    window.history.back( );

                });

                $( ".submit:input:submit" ).prop("disabled", null);

                // Prevent double submits here, and check for empty fields

                $("form").submit(function() {

                    var inputs = $(".validate-empty");
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


</body>
</html>