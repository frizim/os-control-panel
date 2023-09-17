<?php if (count($v['online-users']) == 0): ?>
            <h1>Es ist niemand online!</h1>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Region</th>
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
