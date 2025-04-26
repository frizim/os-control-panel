<form class="validate-form" action="index.php?page=reset-password&token=<?= $v['reset-token'] ?>" method="post">
    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="password" name="password" placeholder="<?= $t('dashboard.profile.newPassword') ?>" required minlength="<?= $v['pwMinLength'] ?>" maxlength="500">
        <span class="warn-invalid"></span>
    </div>

    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="password" name="passwordRepeat" placeholder="<?= $t('dashboard.profile.newPasswordRepeat') ?>" required minlength="<?= $v['pwMinLength'] ?>" maxlength="500">
        <span class="warn-invalid"></span>
    </div>

    <?= $csrf ?>
    <input type="hidden" name="resetToken" value="<?= $v['reset-token'] ?>">
    <button type="submit" class="btn btn-primary w-100 mt-4 pt-3 pb-3 text-uppercase fw-bold text-light fs-5" name="reset-password"><?= $t('resetPassword.submit') ?></button>
</form>
