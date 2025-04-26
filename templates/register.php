<form class="validate-form" action="index.php?page=register" method="post">
    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="text" name="username" placeholder="<?= $t('properties.username') ?>" required minlength="2" maxlength="200">
        <span class="warn-invalid"></span>
    </div>

    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="password" name="password" placeholder="<?= $t('properties.password') ?>" required minlength="<?= $v['pwMinLength'] ?>" maxlength="500">
        <span class="warn-invalid"></span>
    </div>

    <div class="mb-3 validate-input">
        <input class="form-control form-control-lg" type="text" name="email" placeholder="<?= $t('properties.email') ?>" required minlength="5" maxlength="320">
        <span class="warn-invalid"></span>
    </div>

    <div class="mb-3 validate-input">
        <label class="form-label" for="avatar"><?= $t('register.defaultAvatar') ?></label>
        <select class="form-control form-control-lg" name="avatar">
            <?php foreach($v['avatars'] as $k => $val): ?>
                <option value="<?= $k ?>"><?= $k ?></option>
            <?php endforeach ?>
        </select>
    </div>

    <div class="form-check mb-3 validate-input">
        <input class="form-check-input" type="checkbox" name="tos">
        <label class="form-check-label" for="tos"><?= $t('register.confirmTos', ['tosLink' => '<a href="'.$v['tos-url'].' target="_blank">'.$t('register.tos').'</a>']) ?></label>
        <span class="warn-invalid"></span>
    </div>

    <?= $csrf ?>
    <input type="hidden" name="code" value="<?= $v['invcode'] ?>">
    <button type="submit" class="btn btn-primary w-100 mt-4 pt-3 pb-3 text-uppercase fw-bold text-light fs-5"><?= $t('register.submit') ?></button>
</form>
