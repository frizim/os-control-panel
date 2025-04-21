<form class="validate-form" action="index.php?page=reset-password&token=<?= $v['reset-token'] ?>" method="post">
    <h1 class="text-center text-uppercase fw-bold mt-5 mb-5">Neues Passwort festlegen</h1>

    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="password" name="password" placeholder="Passwort" required minlength="6" maxlength="500">
        <span class="warn-invalid"></span>
    </div>

    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="password" name="passwordRepeat" placeholder="Passwort wiederholen" required minlength="6" maxlength="500">
        <span class="warn-invalid"></span>
    </div>

    <?= $csrf ?>
    <input type="hidden" name="resetToken" value="<?= $v['reset-token'] ?>">
    <button type="submit" class="btn btn-primary w-100 mt-4 pt-3 pb-3 text-uppercase fw-bold text-light fs-5" name="reset-password">Passwort ändern</button>
</form>
