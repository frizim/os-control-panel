<form class="login100-form validate-form flex-sb flex-w" action="index.php?page=register" method="post">
    <span class="login100-form-title p-b-51">Registrieren</span>

    <div class="flex-sb-m w-full p-t-3 p-b-24" style="color: red;">
        <?= $v['message'] ?>
    </div>

    <div class="wrap-input100 validate-input m-b-16" data-validate="Bitte gebe dein Benutzernamen an.">
        <input class="input100" type="text" name="username" placeholder="Benutzername">
        <span class="focus-input100"></span>
    </div>

    <div class="wrap-input100 validate-input m-b-16" data-validate="Bitte gebe dein Passwort ein.">
        <input class="input100" type="password" name="password" placeholder="Passwort">
        <span class="focus-input100"></span>
    </div>

    <div class="wrap-input100 validate-input m-b-16" data-validate="Bitte gebe deine E-Mail ein.">
        <input class="input100" type="text" name="email" placeholder="E-Mail">
        <span class="focus-input100"></span>
    </div>

    <div class="flex-sb-m w-full p-t-3 p-b-24">
        Wähle deinen Standardavatar aus:
    </div>

    <div class="wrap-input100 validate-input m-b-16" data-validate="Bitte wähle einen Standardavatar aus.">
        <select class="input100" name="avatar">
            <?php foreach($v['avatars'] as $k => $val): ?>
                <option value="<?= $k ?>"><?= $k ?></option>
            <?php endforeach ?>
        </select>
    </div>

    <div class="wrap-input100" data-validate="Bitte gebe deine E-Mail ein.">
        <input type="checkbox" name="tos"> Ich habe die <a href="<?= $v['tos-url'] ?>" target="_blank">Nutzungsbedingungen</a> gelesen.
        <span class="focus-input100"></span>
    </div>

    <div class="container-login100-form-btn m-t-17">
        <?= $csrf ?>
        <input type="hidden" name="code" value="<?= $v['invcode'] ?>">
        <button type="submit" class="login100-form-btn" name="doRegister">Registrieren</button>
    </div>
</form>
