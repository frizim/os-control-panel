<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\OpenSim;
use Mcp\Middleware\LoginRequiredMiddleware;

class Profile extends \Mcp\RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new LoginRequiredMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {
        $tpl = $this->app->template('profile.php')->parent('__dashboard.php');

        //Prüfe ob IAR grade erstellt wird.
        $iarRunning = false;

        if (isset($_SESSION['iar_created'])) {
            $tpl->unsafeVar('iar-message', '<div class="alert alert-success" role="alert">Deine IAR wird jetzt erstellt und der Download Link wird dir per PM zugesendet.</div>');
            unset($_SESSION['iar_created']);
            $iarRunning = true;
        } else {
            $statementIARCheck = $this->app->db()->prepare('SELECT iarfilename,state,created FROM mcp_iar_state WHERE userID =:userID');
            $statementIARCheck->execute(['userID' => $_SESSION['UUID']]);
            if ($row = $statementIARCheck->fetch()) {
                if ($row['state'] < 2) {
                    $tpl->unsafeVar('iar-message', '<div class="alert alert-danger" role="alert">Aktuell wird eine IAR erstellt.<br>Warte bitte bis du eine PM bekommst.</div>');
                    $iarRunning = true;
                }
                else {
                    $tpl->unsafeVar('iar-message', '<div class="alert alert-success role="alert">Du kannst dir deine IAR (erstellt am '.date('d.m.Y', $row['created']).') <a href="https://'.$this->app->config('domain').'/index.php?api=downloadIar&id='.substr($row['iarfilename'], 0, strlen($row['iarfilename']) - 4).'">hier</a> herunterladen. Sie ist mit dem Passwort <i>password</i> geschützt.</div>');
                }
            }
            $statementIARCheck->closeCursor();
        }

        if ($iarRunning) {
            $tpl->var('iar-button-state', 'disabled');
        }
    
        $opensim = new OpenSim($this->app->db());
    
        $partnerUUID = $opensim->getPartner($_SESSION['UUID']);
        $partnerName = "";
    
        if ($partnerUUID != null) {
            $partnerName = $opensim->getUserName($partnerUUID);
        }
    
        $profileInfo = '';
        if (isset($_SESSION['profile_info'])) {
            $profileInfo = $_SESSION['profile_info'];
            unset($_SESSION['profile_info']);
        }

        $tpl->vars([
            'title' => 'Dein Profil',
            'offline-im-state' => $opensim->allowOfflineIM($_SESSION['UUID']) == "TRUE" ? ' checked' : ' ',
            'firstname' => $_SESSION['FIRSTNAME'],
            'lastname' => $_SESSION['LASTNAME'],
            'username' => $_SESSION['DISPLAYNAME'],
            'partner' => $partnerName,
            'email' => $opensim->getUserMail($_SESSION['UUID']),
            'residents-js-array' => '',
            'message' => $profileInfo
        ])->render();
    }

    public function post(): void
    {
        if (isset($_POST['createIAR'])) {
            $validator = new FormValidator(array()); // CSRF validation only
            if($validator->isValid($_POST)) {
                $validRequest = true;

                $statementIarFile = $this->app->db()->prepare('SELECT iarfilename,state,created FROM mcp_iar_state WHERE userID = ?');
                $statementIarFile->execute([$_SESSION['UUID']]);
                if ($row = $statementIarFile->fetch()) {
                    if ($row['state'] == 2) {
                        unlink($this->app->getDataDir().DIRECTORY_SEPARATOR.'iars'.DIRECTORY_SEPARATOR.$row['iarfilename']);
                    }
                    else {
                        $validRequest = false;
                    }
                }

                if ($validRequest) {
                    $iarname = md5(time().$_SESSION['UUID'] . rand()).".iar";
                
                    $statementIARSTART = $this->app->db()->prepare('INSERT INTO mcp_iar_state (userID, filesize, iarfilename) VALUES (:userID, :filesize, :iarfilename) ON DUPLICATE KEY UPDATE filesize = :replFilesize, state = :replState');
                    $statementIARSTART->execute(['userID' => $_SESSION['UUID'], 'filesize' => 0, 'iarfilename' => $iarname, 'replFilesize' => 0, 'replState' => 0]);
    
                    $_SESSION['iar_created'] = true;
                }
            }
        }
        elseif (isset($_POST['saveProfileData'])) {
            $validator = new FormValidator(array(
                'formInputFeldVorname' => array('regex' => '/^[^\\/<>\s]{1,64}$/'),
                'formInputFeldNachname' => array('regex' => '/^[^\\/<>\s]{1,64}$/'),
                'formInputFeldEMail' => array('regex' => '/^\S{1,64}@\S{1,250}.\S{2,64}$/'),
                'formInputFeldOfflineIM' => array('regex' => '/^(|on)$/'),
                'formInputFeldPartnerName' => array('regex' => '/^[^\\/<>\s]{1,64} [^\\/<>\s]{1,64}$/')
            ));
            
            if ($validator->isValid($_POST)) {
                if(isset($_POST['formInputFeldVorname'])) {
                    $newFirstName = trim($_POST['formInputFeldVorname']);
                    
                    if($newFirstName != "" && $_SESSION['FIRSTNAME'] != $newFirstName) {
                        if($this->setNamePart('FirstName', $newFirstName, 'LastName', isset($_POST['formInputFeldNachname']) && strlen(trim($_POST['formInputFeldNachname'])) > 0 ? $_POST['formInputFeldNachname'] : $_SESSION['LASTNAME'])) {
                            $_SESSION['FIRSTNAME'] = $newFirstName;
                            $_SESSION['USERNAME'] = $_SESSION['FIRSTNAME']." ".$_SESSION['LASTNAME'];
                            $_SESSION['DISPLAYNAME'] = strtoupper($_SESSION['USERNAME']);
                        }
                        else {
                            $_SESSION['profile_info'] = 'Der gewählte Name ist bereits vergeben.';
                        }
                    }
                }
            
                if (isset($_POST['formInputFeldNachname'])) {
                    $newLastName = trim($_POST['formInputFeldNachname']);
                    
                    if ($newLastName != "" && $_SESSION['LASTNAME'] != $newLastName) {
                        if ($this->setNamePart('LastName', $newLastName, 'FirstName', isset($_POST['formInputFeldVorname']) && strlen(trim($_POST['formInputFeldVorname'])) > 0 ? $_POST['formInputFeldVorname'] : $_SESSION['FIRSTNAME'])) {
                            $_SESSION['LASTNAME'] = $newLastName;
                            $_SESSION['USERNAME'] = $_SESSION['FIRSTNAME']." ".$_SESSION['LASTNAME'];
                            $_SESSION['DISPLAYNAME'] = strtoupper($_SESSION['USERNAME']);
                        } else {
                            $_SESSION['profile_info'] = 'Der gewählte Name ist bereits vergeben.';
                        }
                    }
                }
            
                if (isset($_POST['formInputFeldEMail'])) {
                    $newEmail = trim($_POST['formInputFeldEMail']);
            
                    if ($newEmail != "" && $_SESSION['EMAIL'] != $newEmail) {
                        $statement = $this->app->db()->prepare('UPDATE UserAccounts SET Email = :Email WHERE PrincipalID = :PrincipalID');
                        $statement->execute(['Email' => $newEmail, 'PrincipalID' => $_SESSION['UUID']]);
        
                        $statement = $this->app->db()->prepare('UPDATE usersettings SET email = :Email WHERE useruuid = :PrincipalID');
                        $statement->execute(['Email' => $newEmail, 'PrincipalID' => $_SESSION['UUID']]);
        
                        $_SESSION['EMAIL'] = $newEmail;
                    }
                }
            
                if (isset($_POST['formInputFeldOfflineIM']) && $_POST['formInputFeldOfflineIM'] == "on") {
                    $statement = $this->app->db()->prepare('UPDATE usersettings SET imviaemail = :IMState WHERE useruuid = :PrincipalID');
                    $statement->execute(['IMState' => 'true', 'PrincipalID' => $_SESSION['UUID']]);
                } else {
                    $statement = $this->app->db()->prepare('UPDATE usersettings SET imviaemail = :IMState WHERE useruuid = :PrincipalID');
                    $statement->execute(['IMState' => 'false', 'PrincipalID' => $_SESSION['UUID']]);
                }

                if (isset($_POST['formInputFeldPartnerName']) && $_POST['formInputFeldPartnerName'] != "") {
                    $opensim = new OpenSim($this->app->db());

                    $newPartner = trim($_POST['formInputFeldPartnerName']);
                    $currentPartner = $opensim->getPartner($_SESSION['UUID']);
            
                    if ($currentPartner != "") {
                        $currentPartner = $opensim->getUserName($currentPartner);
                    }
            
                    if ($newPartner != "" && $currentPartner != $newPartner) {
                        $newPartnerUUID = $opensim->getUserUUID($newPartner);
            
                        if ($newPartnerUUID != null) {
                            $statement = $this->app->db()->prepare('UPDATE userprofile SET profilePartner = :profilePartner WHERE useruuid = :PrincipalID');
                            $statement->execute(['profilePartner' => $newPartnerUUID, 'PrincipalID' => $_SESSION['UUID']]);
                        }
                    } else {
                        $statement = $this->app->db()->prepare('UPDATE userprofile SET profilePartner = :profilePartner WHERE useruuid = :PrincipalID');
                        $statement->execute(['profilePartner' => '00000000-0000-0000-0000-000000000000', 'PrincipalID' => $_SESSION['UUID']]);
                    }
                }
            }
        } elseif (isset($_POST['savePassword'])) {
            $validator = new FormValidator(array(
                'oldPassword' => array('required' => true, 'regex' => '/^.{1,1000}$/'),
                'newPassword' => array('required' => true, 'regex' => '/^.{1,1000}$/'),
                'newPasswordRepeat' => array('required' => true, 'regex' => '/^.{1,1000}$/')
            ));

            if ($validator->isValid($_POST)) {
                if ($_POST['newPasswordRepeat'] == $_POST['newPassword']) {
                    if (strlen(trim($_POST['newPassword']))  >= $this->app->config('password-min-length')) {
                        if (md5(md5($_POST['oldPassword']).':'.$_SESSION['SALT']) == $_SESSION['PASSWORD']) {
                            $salt = bin2hex(random_bytes(16));
                            $hash = md5(md5(trim($_POST['newPassword'])).':'.$salt);
                            $statement = $this->app->db()->prepare('UPDATE auth SET passwordHash = :PasswordHash, passwordSalt = :PasswordSalt WHERE UUID = :PrincipalID');
                            $statement->execute(['PasswordHash' => $hash, 'PasswordSalt' => $salt, 'PrincipalID' => $_SESSION['UUID']]);
                            $_SESSION['PASSWORD'] = $hash;
                            $_SESSION['SALT'] = $salt;
                            $_SESSION['profile_info'] = 'Neues Passwort gespeichert.';
                        } else {
                            $_SESSION['profile_info'] = 'Das alte Passwort ist nicht richtig!';
                        }
                    } else {
                        $_SESSION['profile_info'] = 'Das neue Passwort muss mindestens '.$this->app->config('password-min-length').' Zeichen lang sein.';
                    }
                } else {
                    $_SESSION['profile_info'] = 'Die neuen Passwörter stimmen nicht überein!';
                }
            } else {
                $_SESSION['profile_info'] = 'Bitte fülle das Formular vollständig aus.';
            }
        } elseif (isset($_POST['deleteAccount'])) {
            $validator = new FormValidator(array(
                'delete-confirm-password' => array('required' => true, 'regex' => '/^.{1,1000}$/'),
                'delete-confirm' => array('required' => true, 'regex' => '/^(|on)$/')
            ));

            if ($validator->isValid($_POST)) {
                if (hash_equals(md5(md5($_POST['delete-confirm-password']).':'.$_SESSION['SALT']), $_SESSION['PASSWORD'])) {
                    $os = new OpenSim($this->app->db());
                    if ($os->deleteUser($_SESSION['UUID'])) {
                        $_SESSION['LOGIN'] = false;
                        session_destroy();
                        header('Location: index.php');
                        die();
                    } else {
                        $_SESSION['profile_info'] = 'Bei der Accountlöschung ist ein Fehler aufgetreten. Bitte versuche es später erneut.';
                    }
                }
                else {
                    $_SESSION['profile_info'] = 'Zur Bestätigung der Accountlöschung musst du dein Passwort richtig eingeben.';
                }
            }
            else {
                $_SESSION['profile_info'] = 'Um deinen Account zu löschen, ist dein aktuelles Passwort und die Bestätigung des Vorgangs erforderlich.';
            }
        }

        header('Location: index.php?page=profile');
    }

    private function setNamePart(string $part, string $value, string $otherPart, string $otherValue): bool
    {
        $query = $this->app->db()->prepare('SELECT 1 FROM UserAccounts WHERE '.$part.' = ? AND '.$otherPart.' = ?');
        $query->execute(array($value, $otherValue));

        if ($query->rowCount() == 0) {
            $statement = $this->app->db()->prepare('UPDATE UserAccounts SET '.$part.' = ? WHERE PrincipalID = ?');
            $statement->execute(array($value, $_SESSION['UUID']));
            return true;
        }

        return false;
    }
}
