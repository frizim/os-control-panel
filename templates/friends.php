<table class="table">
    <thead>
        <tr>
            <th scope="col"><?= $t('properties.name') ?></th>
            <th scope="col"><?= $t('properties.actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($v['friends'] as $friend): ?>
            <tr>
                <td><?= $friend['name'] ?></td>
                <td><form action="index.php?page=friends" method="post"><?= $csrf ?><input type="hidden" name="uuid" value="<?= $friend['uuid'] ?>"><button type="submit" name="remove" class="btn btn-danger btn-sm"><?= $t('dashboard.friends.delete') ?></button></form></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
