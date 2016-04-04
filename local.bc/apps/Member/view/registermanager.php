<?
extract($this->context);
?>

<!-- Always shows a header, even in smaller screens. -->
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class="mdl-layout__header">
        <div class="mdl-layout--large-screen-only mdl-layout__header-row">
        </div>
        <div class="mdl-layout__header-row">
            <h2>Регистрация менеджера в системе</h2>
        </div>
    </header>

    <main class="mdl-layout__content">
        <div class="page-content">
            <div class="mdl-layout--large-screen-only mdl-layout__header-row strokaimya">
                <h4>Регистрация в системе Biocon ERP в качестве менеджера</h4>
            </div>
            <div class="mdl-layout--large-screen-only mdl-layout__header-row strokaimya">

            </div>
            <br>
            <div class="mdl-layout--large-screen-only mdl-layout__header-row ">
                <form action="/User/Register" data-managedform="yes" data-onsuccess="userRegister"  data-onerror="capaCreateError">
                    <input type="hidden" name="external" data-selector="external" value="0">
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield-demo">
                        <input class="mdl-textfield__input" type="text" id="sample3" name="email" data-selector="email" required="required"/>
                        <label class="mdl-textfield__label" for="sample3">Введите email</label>
                    </div>
                    <br>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield-demo">
                        <input class="mdl-textfield__input" type="text" id="sample3" name="providedpassword" data-selector="providedpassword" required="required"/>
                        <label class="mdl-textfield__label" for="sample3">Введите пароль</label>
                        <p></p>
                    </div>
                    <br>
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label textfield-demo">
                        <input class="mdl-textfield__input" type="text" id="sample3" name="providedpasswordcopy" data-selector="providedpasswordcopy" required="required"/>
                        <label class="mdl-textfield__label" for="sample3">Повторите пароль</label>
                        <p></p>
                    </div>
                    <br>
                    <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" type="submit">
                        Зарегистрироваться
                    </button>

                </form>
            </div>


        </div>
    </main>
</div>