<table class="table">
    <thead>
        <tr>
            <th scope="col"><?= $t('dashboard.groups.name') ?></th>
            <th scope="col"><?= $t('dashboard.groups.founder') ?></th>
            <th scope="col"><?= $t('properties.actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($v["groups"] as $group): ?>
            <tr>
                <td><?= $group["name"] ?></td>
                <td><?= $group["founder"] ?></td>
                <td><form action="index.php?page=groups" method="post"><?= $v["csrf"] ?><input type="hidden" name="group" value="<?= $group["uuid"] ?>"><button type="submit" name="leave" class="btn btn-danger btn-sm"><?= $t('dashboard.groups.leave') ?></button></form></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
