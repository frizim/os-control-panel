<div>
    Hier kannst du die UUID von deinem Avatar ändern und später jederzeit wieder zurückwechseln. <br/>
    Inventar und Gruppen bleiben dabei erhalten. <br/>
    Jede Identität hat ein eigenes Aussehen, ein eigenes Profil und eine eigene Freundesliste.
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($v["identities"] as $identity): ?>
                        <tr>
                            <td><?= $identity["name"] ?></td>
                            <?php if($identity["active"]): ?>
                                <td><span class="badge badge-info">Aktiv</span></td>
                            <?php else: ?>
                                <td data-uuid="<?= $identity["uuid"] ?>">
                                    <button name="enableIdent" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#isc">Aktivieren</button>
                                    <button name="deleteIdent" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#idc">Löschen</button>
                                </td>
                            <?php endif ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <p>Hier kannst du eine neue Identität erstellen.</p>
            <div>
                <form action="index.php?page=identities" method="post">
                    <div class="mb-3">
                        <label class="form-label" for="username">Name</label>
                        <input class="form-control" type="text" name="username" id="username" placeholder="Name" required minlength="2" autocomplete="off">
                    </div>
                    <?= $csrf ?>
                    <button type="submit" name="createIdent" class="btn btn-primary btn-lg">Erstelle Identität</button>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="isc" tabindex="-1" aria-labelledby="iscLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="iscLabel">Identitätswechsel bestätigen</h5>
                    <a class="btn btn-close text-danger fs-2" href="#" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></a>
                </div>
                <div class="modal-body">
                    Folgende Daten sind für alle deine Identitäten gleich:
                    <ul>
                        <li>Passwort</li>
                        <li>Inventar</li>
                        <li>Gruppen</li>
                    </ul>
                    Dagegen besitzt du nach dem Wechsel die folgenden, separaten Einstellungen deiner neuen Identität:
                    <ul>
                        <li>Name</li>
                        <li>User-Level</li>
                        <li>Profil</li>
                        <li>Freundesliste</li>
                    </ul>
                    Möchtest du deine aktive Identität von <b><?= $v['activeIdent'] ?></b> zu <b id="isc-ident-name"></b> wechseln? Du kannst jederzeit zurückwechseln.
                </div>
                <div class="modal-footer">
                    <form action="index.php?page=identities" method="post">
                        <input type="hidden" value="" name="uuid" id="isc-ident-uuid">
                        <?= $csrf ?>
                        <button type="submit" name="enableIdent" class="btn btn-primary btn-success">Identität wechseln</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="idc" tabindex="-1" aria-labelledby="idcLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="idcLabel">Löschung der Identität bestätigen</h5>
                    <a class="btn btn-close text-danger fs-2" href="#" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></a>
                </div>
                <div class="modal-body">
                    Wenn du eine Identität löschst, werden folgende zu dieser zugehörige Daten gelöscht:
                    <ul>
                        <li>Name</li>
                        <li>User-Level</li>
                        <li>Profil</li>
                        <li>Freundesliste</li>
                    </ul>
                    Deine anderen Account-Daten sind davon nicht betroffen.<br>
                    Möchtest du die Identität <b id="idc-ident-name"></b> wirklich löschen?
                </div>
                <div class="modal-footer">
                    <form action="index.php?page=identities" method="post">
                        <input type="hidden" value="" name="uuid" id="idc-ident-uuid">
                        <?= $csrf ?>
                        <button type="submit" name="deleteIdent" class="btn btn-primary btn-danger">Identität löschen</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="./js/identities.js" defer></script>
