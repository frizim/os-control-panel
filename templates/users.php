<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Vorname</th>
                        <th scope="col">Nachname</th>
                        <th scope="col">Level</th>
                        <th scope="col">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($v["users"] as $user): ?>
                        <tr>
                            <td><?= $user["firstName"] ?></td>
                            <td><?= $user["lastName"] ?></td>
                            <td><?= $user["level"] ?></td>
                            <td>
                                <form action="index.php?page=users" method="post">
                                    <?= $v["csrf"] ?>
                                    <input type="hidden" name="userid" value="<?= $user["uuid"] ?>">
                                    <button type="submit" name="genpw" class="btn btn-link btn-sm">PASSWORT ZURÜCKSETZEN</button> <button type="submit" name="deluser" class="btn btn-link btn-sm text-danger">LÖSCHEN</button>
                                </form>
                            </td>
                        </tr>
                        <?php foreach($user["identities"] as $identity): ?>
                            <tr class="ident-row">
                                <td><?= $identity["firstName"] ?></td>
                                <td><?= $identity["lastName"] ?></td>
                                <td><?= $identity["level"] ?></td>
                                <td>
                                    <form action="index.php?page=users" method="post">
                                        <?= $v["csrf"] ?>
                                        <input type="hidden" name="userid" value="<?= $user["uuid"] ?>">
                                        <input type="hidden" name="identid" value="<?= $identity["uuid"] ?>">
                                        <button type="submit" name="delident" class="btn btn-link btn-sm">Identität löschen</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <div>
                Hier kannst du einen Invite-Link erstellen.<br />
                Jeder der solch einen Link bekommt, kann sich im Grid registrieren.<br/>
                Der Link ist einzigartig und funktioniert nur einmalig.
            </div>
            
            <div>
                <form action="index.php?page=users" method="post">
                    <div class="row mt-2">
                        <div class="col">
                            <label class="form-label" for="linkOutput">Invite-Link</label>
                            <input type="text" class="form-control" id="linkOutput" name="formLink" value="<?= $v['invite-link'] ?>">
                        </div>
                    </div>
            
                    <div class="row mt-2">
                        <div class="col">
                            <?= $csrf ?>
                            <button type="submit" name="generateLink" class="btn btn-primary btn-lg">Link Generieren</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
