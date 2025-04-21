<table class="table">
    <thead>
        <tr>
            <th scope="col">Name</th>
            <th scope="col">Gründer</th>
            <th scope="col">Aktionen</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($v["groups"] as $group): ?>
            <tr>
                <td><?= $group["name"] ?></td>
                <td><?= $group["founder"] ?></td>
                <td><form action="index.php?page=groups" method="post"><?= $v["csrf"] ?><input type="hidden" name="group" value="<?= $group["uuid"] ?>"><button type="submit" name="leave" class="btn btn-danger btn-sm">VERLASSEN</button></form></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
