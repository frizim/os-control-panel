<form class="login100-form validate-form flex-sb flex-w" action="index.php?page=login" method="post">
    <span class="login100-form-title p-b-51">
        Login
    </span>

    <div class="flex-sb-m w-full p-t-3 p-b-24" style="color: <?= $v['message-color'] ?>;">
        <?= $v['message'] ?>
    </div>

    <div class="wrap-input100 validate-input m-b-16" data-validate="Bitte gebe dein Benutzernamen an.">
        <input class="input100" type="text" name="username" value="<?= $v['last-username'] ?>" placeholder="Benutzername">
        <span class="focus-input100"></span>
    </div>

    <div class="wrap-input100 validate-input m-b-16" data-validate="Bitte gebe dein Passwort ein.">
        <input class="input100" type="password" name="password" placeholder="Passwort">
        <span class="focus-input100"></span>
    </div>

    <div class="flex-sb-m w-full p-t-3 p-b-24">
        <div>
             
        </div>

        <div>
            <a href="index.php?page=forgot" class="txt1">Passwort vergessen?</a>
        </div>
    </div>

    <div class="container-login100-form-btn m-t-17">
        <?= $v['csrf'] ?>
        <button class="login100-form-btn" name="login">
            Anmelden
        </button>
    </div>
</form>
