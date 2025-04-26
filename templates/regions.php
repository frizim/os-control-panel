<table class="table align-middle">
    <thead>
        <tr>
            <th scope="col"><?= $t('dashboard.regions.name') ?></th>
            <th scope="col"><?= $t('dashboard.regions.owner') ?></th>
            <th scope="col"><?= $t('dashboard.regions.position') ?></th>
            <th scope="col"><?= $t('properties.actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($v['regions'] as $region): ?>
            <tr>
                <td><?= $region['name']?>
                    <div class="blockquote-footer mt-1 mb-0">
                        <?php if(!empty($region['stats'])): ?>
                            <?= $t('dashboard.regions.stats', $region['stats']) ?>
                        <?php else: ?>
                            <?= $t('dashboard.regions.noStats') ?>
                        <?php endif ?>
                    </div>
                </td>
                <td><?= $region['owner_name'] ?></td>
                <td><?= $region['locX'] ?> / <?= $region['locY'] ?></td>
                <td><form action="index.php?page=regions<?= $v['showall'] ?>" method="post"><?= $csrf ?><input type="hidden" name="region" value="<?= $region['uuid'] ?>"><button type="submit" name="remove" class="btn btn-link btn-sm p-0 mt-0"><?= $t('dashboard.regions.delete') ?></button></form></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
