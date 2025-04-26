<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col"><?= $t('properties.firstName') ?></th>
                        <th scope="col"><?= $t('properties.lastName') ?></th>
                        <th scope="col"><?= $t('properties.level') ?></th>
                        <th scope="col"><?= $t('properties.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($v['users'] as $user): ?>
                        <tr class="pt-2 pb-2">
                            <td><?= $user['firstName'] ?></td>
                            <td><?= $user['lastName'] ?></td>
                            <td><?= $user['level'] ?></td>
                            <td>
                                <form action="index.php?page=users" method="post">
                                    <?= $v['csrf'] ?>
                                    <input type="hidden" name="userid" value="<?= $user['uuid'] ?>">
                                    <button type="submit" name="genpw" class="btn btn-link btn-sm p-0 mt-0"><?= $t('dashboard.admin.users.resetPassword') ?></button> <button type="submit" name="deluser" class="btn btn-link btn-sm text-danger p-0 mt-0"><?= $t('dashboard.admin.users.delete') ?></button>
                                </form>
                            </td>
                        </tr>
                        <?php foreach($user['identities'] as $identity): ?>
                            <tr class="ident-row">
                                <td><?= $identity['firstName'] ?></td>
                                <td><?= $identity['lastName'] ?></td>
                                <td><?= $identity['level'] ?></td>
                                <td>
                                    <form action="index.php?page=users" method="post">
                                        <?= $v['csrf'] ?>
                                        <input type="hidden" name="userid" value="<?= $user['uuid'] ?>">
                                        <input type="hidden" name="identid" value="<?= $identity['uuid'] ?>">
                                        <button type="submit" name="delident" class="btn btn-link btn-sm p-0 mt-0"><?= $t('dashboard.admin.users.deleteIdentity') ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <div><?= $t('dashboard.admin.users.createInvite.help') ?></div>
            
            <div>
                <form action="index.php?page=users" method="post">
                    <div class="row mt-2">
                        <div class="col">
                            <label class="form-label" for="linkOutput"><?= $t('dashboard.admin.users.createInvite.output') ?></label>
                            <input type="text" class="form-control" id="linkOutput" name="formLink" value="<?= $v['invite-link'] ?>">
                        </div>
                    </div>
            
                    <div class="row mt-2">
                        <div class="col">
                            <?= $csrf ?>
                            <button type="submit" name="generateLink" class="btn btn-primary btn-lg"><?= $t('dashboard.admin.users.createInvite.create') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
