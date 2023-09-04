<div>
    Hier kannst du die UUID von deinem Avatar ändern und später jederzeit wieder zurückwechseln. <br>
    Inventar und Gruppen bleiben dabei erhalten. <br>
    Jede Identität hat ein eigenes Aussehen, ein eigenes Profil und eine eigene Freundesliste.<br>
    Nach der Änderung musst du dich neu anmelden.<br>

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
</div>
