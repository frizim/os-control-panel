<form class="login100-form validate-form flex-sb flex-w" action="index.php?page=forgot" method="post">
    <span class="login100-form-title p-b-51">
        Passwort vergessen
    </span>

    <div class="flex-sb-m w-full p-t-3 p-b-24" style="color: <?= $v['message-color'] ?>;">
        <?= $v['message'] ?>
    </div>

    <div class="wrap-input100 validate-input m-b-16" data-validate="Bitte gebe deinen Benutzernamen an.">
        <input class="input100" type="text" name="username" placeholder="Benutzername">
        <span class="focus-input100"></span>
    </div>

    <div class="wrap-input100 validate-input m-b-16" data-validate="Bitte gebe deine E-Mail-Adresse ein.">
        <input class="input100" type="email" name="email" placeholder="E-Mail">
        <span class="focus-input100"></span>
    </div>

    <div class="container-login100-form-btn m-t-17">
        <?= $v['csrf'] ?>
        <button class="login100-form-btn" name="forgot-request">
            Absenden
        </button>
    </div>
</form>
