<form class="validate-form" action="index.php?page=forgot" method="post">
    <h1 class="text-center text-uppercase fw-bold mt-5 mb-5">Passwort vergessen</h1>

    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="text" name="username" value="<?= $v['last-username'] ?>" placeholder="Benutzername" required minlength="2">
        <span class="warn-invalid"></span>
    </div>

    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="text" name="email" placeholder="E-Mail" required minlength="5" maxlength="320">
        <span class="warn-invalid"></span>
    </div>
    
    <?= $csrf ?>
    <button type="submit" class="btn btn-primary w-100 mt-4 pt-3 pb-3 text-uppercase fw-bold text-light fs-5" name="forgot-request">Absenden</button>
</form>
