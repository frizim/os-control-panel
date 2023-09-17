<table class="table">
    <thead>
        <tr>
            <th scope="col">Region Name</th>
            <th scope="col">Eigentümer</th>
            <th scope="col">Position</th>
            <th scope="col">Aktionen</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($v['regions'] as $region): ?>
            <tr>
                <td><?= $region['name']?>
                    <div class="blockquote-footer">
                        <?php if(!empty($region['stats'])): ?>
                            Prims: <?= $region['stats']['Prims'] ?>; RAM-Nutzung: <?= $region['stats']['ProcMem'] ?>; SIM/PHYS FPS: <?= $region['stats']['SimFPS'] ?> / <?= $region['stats']['PhyFPS'] ?> (<?= $stats['RegionVersion'] ?>)
                        <?php else: ?>
                            Keine Statistik verfügbar
                        <?php endif ?>
                    </div>
                </td>
                <td><?= $region['owner_name'] ?></td>
                <td><?= $region['locX'] ?> / <?= $region['locY'] ?></td>
                <td><form action="index.php?page=regions<?= $v['showall'] ?>" method="post"><?= $csrf ?><input type="hidden" name="region" value="<?= $region['uuid'] ?>"><button type="submit" name="remove" class="btn btn-link btn-sm">LÖSCHEN</button></form></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
