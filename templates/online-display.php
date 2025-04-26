<?php if (count($v['online-users']) == 0): ?>
    <h1><?= $t('dashboard.user-online-state.noUsers') ?></h1>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th><?= $t('properties.name') ?></th>
                <th><?= $t('properties.region') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($v['online-users'] as $user): ?>
                <tr>
                    <td><?= $user['name'] ?></td>
                    <td><?= $user['region'] ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>
