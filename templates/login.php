<form class="validate-form" action="index.php?page=login" method="post">
    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="text" name="username" value="<?= $v['last-username'] ?>" placeholder="<?= $t('properties.username') ?>" required minlength="2">
        <span class="warn-invalid"></span>
    </div>

    <div class="validate-input">
        <input class="form-control form-control-lg" type="password" name="password" placeholder="<?= $t('properties.password') ?>" required>
        <span class="warn-invalid"></span>
    </div>
    <a href="index.php?page=forgot" class="btn btn-link text-decoration-none ms-auto"><?= $t('login.forgotPassword') ?></a>

    <?= $csrf ?>
    <button type="submit" class="btn btn-primary w-100 mt-4 pt-3 pb-3 text-uppercase fw-bold text-light fs-5" name="login"><?= $t('login.submit') ?></button>
</form>
