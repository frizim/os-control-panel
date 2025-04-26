<div><?= $t('dashboard.identities.help') ?></div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col"><?= $t('properties.name') ?></th>
                        <th scope="col"><?= $t('properties.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($v['identities'] as $identity): ?>
                        <tr>
                            <td><?= $identity['name'] ?></td>
                            <?php if($identity['active']): ?>
                                <td><span class="badge badge-info"><?= $t('dashboard.identities.active') ?></span></td>
                            <?php else: ?>
                                <td data-uuid="<?= $identity['uuid'] ?>">
                                    <button name="enableIdent" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#isc"><?= $t('dashboard.identities.activate') ?></button>
                                    <button name="deleteIdent" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#idc"><?= $t('dashboard.identities.delete.title') ?></button>
                                </td>
                            <?php endif ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <p><?= $t('dashboard.identities.create.help') ?></p>
            <div>
                <form action="index.php?page=identities" method="post">
                    <div class="mb-3">
                        <label class="form-label" for="username"><?= $t('properties.name') ?></label>
                        <input class="form-control" type="text" name="username" id="username" required minlength="2" autocomplete="off">
                    </div>
                    <?= $csrf ?>
                    <button type="submit" name="createIdent" class="btn btn-primary btn-lg"><?= $t('dashboard.identities.create') ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="isc" tabindex="-1" aria-labelledby="iscLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="iscLabel"><?= $t('dashboard.identities.switch.confirm') ?></h5>
                    <a class="btn btn-close text-danger fs-2" href="#" data-bs-dismiss="modal" aria-label="<?= $t('common.close') ?>"><span aria-hidden="true">×</span></a>
                </div>
                <div class="modal-body">
                    <?= $t('dashboard.identities.switch.common') ?>
                    <ul>
                        <li><?= $t('properties.password') ?></li>
                        <li><?= $t('properties.inventory') ?></li>
                        <li><?= $t('properties.groups') ?></li>
                    </ul>
                    <?= $t('dashboard.identities.switch.changing') ?>
                    <ul>
                        <li><?= $t('properties.name') ?></li>
                        <li><?= $t('properties.level') ?></li>
                        <li><?= $t('properties.profile') ?></li>
                        <li><?= $t('properties.friends') ?></li>
                    </ul>
                    <?= $t('dashboard.identities.switch.prompt', ['current' => $v['activeIdent'], 'new' => '']) ?>
                </div>
                <div class="modal-footer">
                    <form action="index.php?page=identities" method="post">
                        <input type="hidden" value="" name="uuid" id="isc-ident-uuid">
                        <?= $csrf ?>
                        <button type="submit" name="enableIdent" class="btn btn-primary btn-success"><?= $t('dashboard.identities.switch.title') ?></button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= $t('common.cancel') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="idc" tabindex="-1" aria-labelledby="idcLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="idcLabel"><?= $t('dashboard.identities.delete.confirm') ?></h5>
                    <a class="btn btn-close text-danger fs-2" href="#" data-bs-dismiss="modal" aria-label="<?= $t('common.close') ?>"><span aria-hidden="true">×</span></a>
                </div>
                <div class="modal-body">
                    <?= $t('dashboard.identities.delete.removing') ?>
                    <ul>
                        <li><?= $t('properties.name') ?></li>
                        <li><?= $t('properties.level') ?></li>
                        <li><?= $t('properties.profile') ?></li>
                        <li><?= $t('properties.friends') ?></li>
                    </ul>
                    <?= $t('dashboard.identities.delete.prompt', ['identity' => '']) ?>
                </div>
                <div class="modal-footer">
                    <form action="index.php?page=identities" method="post">
                        <input type="hidden" value="" name="uuid" id="idc-ident-uuid">
                        <?= $csrf ?>
                        <button type="submit" name="deleteIdent" class="btn btn-primary btn-danger"><?= $t('dashboard.identities.delete.button') ?></button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= $t('common.cancel') ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="./js/identities.js" defer></script>
