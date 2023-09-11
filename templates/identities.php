<div>
    Hier kannst du die UUID von deinem Avatar ändern und später jederzeit wieder zurückwechseln. <br>
    Inventar und Gruppen bleiben dabei erhalten. <br>
    Jede Identität hat ein eigenes Aussehen, ein eigenes Profil und eine eigene Freundesliste.
</div>
<br><?= $v['message'] ?><br>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <?= $v['ident-list'] ?>
        </div>
        <div class="col-md-6">
            <div style="width: 400px; margin: auto; left: 50%;">
                Hier kannst du eine neue Identität erstellen.
            </div>

            <div style="width: 400px; margin: auto; left: 50%;">
                <form action="index.php?page=identities" method="post">
                    <div class="row" style="margin-top: 15px;">
                        <div class="col">
                            <label for="newName">Name</label>
                            <input type="text" class="form-control" id="newName" name="newName" placeholder="Name">
                        </div>
                    </div>

                    <div class="row" style="margin-top: 15px;">
                        <div class="col">
                            <?= $v['csrf'] ?>
                            <button type="submit" name="createIdent" class="btn btn-primary btn-lg">Erstelle Identität</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="isc" tabindex="-1" aria-labelledby="iscLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="iscLabel">Identitätswechsel bestätigen</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
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
                        <?= $v['csrf'] ?>
                        <button type="submit" name="enableIdent" class="btn btn-primary btn-success">Identität wechseln</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
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
                        <?= $v['csrf'] ?>
                        <button type="submit" name="deleteIdent" class="btn btn-primary btn-danger">Identität löschen</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Abbrechen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $v['custom-js'] = '<script src="./js/identities.js"></script>' ?>