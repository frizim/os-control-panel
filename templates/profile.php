<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div>
                <form action="index.php?page=profile" method="post">
                    <div class="row">
                        <div class="col">
                            <label class="form-label" for="inputVorname">Vorname</label>
                            <input type="text" class="form-control" id="inputVorname" name="Vorname" value="<?= $v['firstname'] ?>">
                        </div>
                        <div class="col">
                            <label class="form-label" for="inputNachname">Nachname</label>
                            <input type="text" class="form-control" id="inputNachname" name="Nachname" value="<?= $v['lastname'] ?>">
                        </div>
                    </div>
            
                    <div class="row mt-2">
                        <div class="col">
                            <label class="form-label" for="inputEmail">E-Mail</label>
                            <input type="email" class="form-control" id="inputEmail" name="EMail" value="<?= $v['email'] ?>" autocomplete="email">
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <div class="form-check">
                            <input class="form-check-input" name="OfflineIM" type="checkbox" id="gridCheck"<?= $v['offline-im-state'] ?>>
                            <label class="form-check-label" for="gridCheck"> Offline IM</label>
                        </div>
                    </div>
            
                    <div class="row mt-2">
                        <div class="col">
                            <label class="form-label" for="inputpartner">Partner</label>
                            <input type="text" class="form-control" name="PartnerName" id="inputpartner" value="<?= $v['partner'] ?>">
                        </div>
                    </div>
            
                    <div class="row mt-2">
                        <div class="col">
                            <?= $csrf ?>
                            <button type="submit" name="saveProfileData" class="btn btn-primary btn-lg">Speichern</button>
                        </div>
                    </div>
                </form>
                <div class="row mt-2">
                    <div class="col">
                        <hr>
                    </div>
                </div>
                <form action="index.php?page=profile" method="post">
                    <div class="row">
                        <div class="col">
                            <label class="form-label" for="oldPassword">Altes Passwort</label>
                            <input type="text" class="form-control" id="oldPassword" name="oldPassword">
                        </div>
                    </div>
            
                    <div class="row mt-2">
                        <div class="col">
                            <label class="form-label" for="PasswordNew">Neues Passwort</label>
                            <input type="text" class="form-control" id="PasswordNew" name="newPassword">
                        </div>
                    </div>
            
                    
                    <div class="row mt-2">
                        <div class="col">
                            <label class="form-label" for="PasswordNewRepeat">Neues Passwort wiederholen</label>
                            <input type="text" class="form-control" id="PasswordNewRepeat" name="newPasswordRepeat">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col">
                            <?= $csrf ?>
                            <button type="submit" name="savePassword" class="btn btn-primary btn-lg">Speichern</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div>
                <p class="lead"><b>IAR-Backup</b></p>
                <?php if(strlen($v["iar-message"]) > 0): ?>
                <p class="text-center">
                    <div class="alert alert-<?= $v["iar-status"] ?>" role="alert">
                        <?= $v["iar-message"] ?>
                        <?php if(strlen($v["iar-link"] > 0)): ?> <a href="<?= $v["iar-link"] ?>">Download</a> <?php endif ?>
                    </div>
                </p>
                <?php endif ?>
                Hier kannst du eine IAR deines Inventars erstellen.<br>
                Dies wird einige Zeit dauern. Du bekommst eine PM mit einem Downloadlink sobald deine IAR fertig erstellt wurde.

                <form action="index.php?page=profile" method="post">
                    <div class="row mt-2">
                        <div class="col">
                            <?= $csrf ?>
                            <p class="text-center"><button type="submit" name="createIAR" class="btn btn-primary btn-lg" <?= $v['iar-button-state'] ?>>IAR erstellen</button></p>
                        </div>
                    </div>
                </form>

                <hr class="mt-2" />

                <p class="lead"><b>Account löschen</b></p>
                <p>Du kannst deinen eigenen Account löschen. Dies wird sofort ausgeführt. Deine Daten, einschließlich Inventar, Identitäten und Freundesliste, können danach nicht wiederhergestellt werden.</p>
                <form action="index.php?page=profile" method="post">
                    <div class="row mt-2">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="delete-confirm-password">Aktuelles Passwort</label>
                                <input type="password" class="form-control" id="delete-confirm-password" name="delete-confirm-password" required minlength="6" autocomplete="current-password">
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" name="delete-confirm" type="checkbox" id="delete-confirm" required>
                                    <label class="form-check-label" for="delete-confirm">Ich möchte meinen Account, mein Inventar und alle sonstigen Benutzerdaten von mir unwiderruflich löschen lassen.</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $csrf ?>
                            <p class="text-center"><button type="submit" name="deleteAccount" class="btn btn-danger btn-lg">Account löschen</button></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
