<div class="container-fluid">
	<div class="row">
        <div class="col-md-6">
            <?= strlen($v['message']) == 0 ? '' : '<div class="alert alert-danger" role="alert">'.$v['message'].'</div>'?>
            <?= $v['user-list'] ?>
        </div>
		<div class="col-md-6">
            <div style="width: 400px; margin: auto; left: 50%;">
                Hier kannst du einen Invite-Link erstellen.<br>
                Jeder der solch einen Link bekommt, kann sich im Grid registrieren.
                Der Link ist einzigartig und funktioniert nur einmalig.<br>
                Nach Aufruf des Links muss ein Name, Passwort und Standardavatar ausgewählt werden.
            </div>
            
            <div style="width: 400px; margin: auto; left: 50%;">
                <form action="index.php?page=users" method="post">
                    <div class="row" style="margin-top: 15px;">
                        <div class="col">
                            <label for="linkOutput">Invite-Link:</label>
                            <input type="text" class="form-control" id="linkOutput" name="formLink" value="<?= $v['invite-link'] ?>">
                        </div>
                    </div>
            
                    <div class="row" style="margin-top: 15px;">
                        <div class="col">
                            <?= $v['csrf'] ?>
                            <button type="submit" name="generateLink" class="btn btn-primary btn-lg">Link Generieren</button>
                        </div>
                    </div>
                </form>
            </div>
		</div>
	</div>
</div>