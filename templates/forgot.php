<form class="validate-form" action="index.php?page=forgot" method="post">
    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="text" name="username" value="<?= $v['last-username'] ?>" placeholder="<?= $t('properties.username') ?>" required minlength="2">
        <span class="warn-invalid"></span>
    </div>

    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="text" name="email" placeholder="<?= $t('properties.email') ?>" required minlength="6" maxlength="320">
        <span class="warn-invalid"></span>
    </div>
    
    <?= $csrf ?>
    <button type="submit" class="btn btn-primary w-100 mt-4 pt-3 pb-3 text-uppercase fw-bold text-light fs-5" name="forgot-request"><?= $t('forgotPassword.submit') ?></button>
</form>
