<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div>
                <form action="index.php?page=profile" method="post">
                    <div class="row">
                        <div class="col">
                            <label class="form-label" for="inputVorname"><?= $t('properties.firstName') ?></label>
                            <input type="text" class="form-control" id="inputVorname" name="Vorname" value="<?= $v['firstname'] ?>">
                        </div>
                        <div class="col">
                            <label class="form-label" for="inputNachname"><?= $t('properties.lastName') ?></label>
                            <input type="text" class="form-control" id="inputNachname" name="Nachname" value="<?= $v['lastname'] ?>">
                        </div>
                    </div>
            
                    <div class="row mt-2">
                        <div class="col">
                            <label class="form-label" for="inputEmail"><?= $t('properties.email') ?></label>
                            <input type="email" class="form-control" id="inputEmail" name="EMail" value="<?= $v['email'] ?>" autocomplete="email">
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <div class="form-check">
                            <input class="form-check-input" name="OfflineIM" type="checkbox" id="gridCheck"<?= $v['offline-im-state'] ?>>
                            <label class="form-check-label" for="gridCheck"><?= $t('properties.offlineIm') ?></label>
                        </div>
                    </div>
            
                    <div class="row mt-2">
                        <div class="col">
                            <label class="form-label" for="inputpartner"><?= $t('properties.partner') ?></label>
                            <input type="text" class="form-control" name="PartnerName" id="inputpartner" value="<?= $v['partner'] ?>">
                        </div>
                    </div>
            
                    <div class="row mt-2">
                        <div class="col">
                            <?= $csrf ?>
                            <button type="submit" name="saveProfileData" class="btn btn-primary btn-lg"><?= $t('dashboard.profile.save') ?></button>
                        </div>
                    </div>
                </form>
                <div class="row mt-2">
                    <div class="col">
                        <hr>
                    </div>
                </div>
                <form action="index.php?page=profile" method="post">
                    <div class="row">
                        <div class="col">
                            <label class="form-label" for="oldPassword"><?= $t('dashboard.profile.oldPassword') ?></label>
                            <input type="text" class="form-control" id="oldPassword" name="oldPassword">
                        </div>
                    </div>
            
                    <div class="row mt-2">
                        <div class="col">
                            <label class="form-label" for="PasswordNew"><?= $t('dashboard.profile.newPassword') ?></label>
                            <input type="text" class="form-control" id="PasswordNew" name="newPassword">
                        </div>
                    </div>
            
                    
                    <div class="row mt-2">
                        <div class="col">
                            <label class="form-label" for="PasswordNewRepeat"><?= $t('dashboard.profile.newPasswordRepeat') ?></label>
                            <input type="text" class="form-control" id="PasswordNewRepeat" name="newPasswordRepeat">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col">
                            <?= $csrf ?>
                            <button type="submit" name="savePassword" class="btn btn-primary btn-lg"><?= $t('dashboard.profile.save') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div>
                <p class="lead"><b><?= $t('dashboard.profile.iar.title') ?></b></p>
                <?php if(strlen($v['iar-message']) > 0): ?>
                <p class="text-center">
                    <div class="alert alert-<?= $t($v['iar-status'], $v['iar-status-params'] ? $v['iar-status-params'] : []) ?>" role="alert">
                        <?= $v["iar-message"] ?>
                        <?php if(strlen($v['iar-link'] > 0)): ?> <a href="<?= $v['iar-link'] ?>"><?= $t('dashboard.profile.iar.download') ?></a> <?php endif ?>
                    </div>
                </p>
                <?php endif ?>
                <?= $t('dashboard.profile.iar.help') ?>
                <form action="index.php?page=profile" method="post">
                    <div class="row mt-2">
                        <div class="col">
                            <?= $csrf ?>
                            <p class="text-center"><button type="submit" name="createIAR" class="btn btn-primary btn-lg" <?= $v['iar-button-state'] ?>><?= $t('dashboard.profile.iar.create') ?></button></p>
                        </div>
                    </div>
                </form>

                <hr class="mt-2" />

                <p class="lead"><b><?= $t('dashboard.profile.delete.title') ?></b></p>
                <p><?= $t('dashboard.profile.delete.help') ?></p>
                <form action="index.php?page=profile" method="post">
                    <div class="row mt-2">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="delete-confirm-password"><?= $t('dashboard.profile.delete.password') ?></label>
                                <input type="password" class="form-control" id="delete-confirm-password" name="delete-confirm-password" required autocomplete="current-password">
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" name="delete-confirm" type="checkbox" id="delete-confirm" required>
                                    <label class="form-check-label" for="delete-confirm"><?= $t('dashboard.profile.delete.confirm') ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $csrf ?>
                            <p class="text-center"><button type="submit" name="deleteAccount" class="btn btn-danger btn-lg"><?= $t('dashboard.profile.delete.submit') ?></button></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
