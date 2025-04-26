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
            $tpl->vars([
                'iar-status' => 'success',
                'iar-message' => 'dashboard.profile.iar.started'
            ]);
            unset($_SESSION['iar_created']);
            $iarRunning = true;
        } else {
            $statementIARCheck = $this->app->db()->prepare('SELECT iarfilename,state,created FROM mcp_iar_state WHERE userID =:userID');
            $statementIARCheck->execute(['userID' => $_SESSION['UUID']]);
            if ($row = $statementIARCheck->fetch()) {
                if ($row['state'] < 2) {
                    $tpl->vars([
                        'iar-status' => 'warning',
                        'iar-message' => 'dashboard.profile.iar.inprogress'
                    ]);
                    $iarRunning = true;
                }
                else {
                    $tpl->vars([
                        'iar-status' => 'success',
                        'iar-message' => 'dashboard.profile.iar.done',
                        'iar-message-params' => [
                            'created' => date('d.m.Y', $row['created']),
                            'iar-link' => 'https://'.$this->app->config('domain').'/index.php?api=downloadIar&id='.substr($row['iarfilename'], 0, strlen($row['iarfilename']) - 4)
                        ]
                    ]);
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
        $profileInfoParams = null;
        if (isset($_SESSION['profile_info'])) {
            $profileInfo = $_SESSION['profile_info'];
            $profileInfoParams = $_SESSION['profile_info_params'];
            unset($_SESSION['profile_info']);
            unset($_SESSION['profile_info_params']);
        }

        $tpl->vars([
            'title' => 'dashboard.profile.title',
            'offline-im-state' => $opensim->allowOfflineIM($_SESSION['UUID']) == "TRUE" ? ' checked' : ' ',
            'firstname' => $_SESSION['FIRSTNAME'],
            'lastname' => $_SESSION['LASTNAME'],
            'username' => $_SESSION['DISPLAYNAME'],
            'partner' => $partnerName,
            'email' => $opensim->getUserMail($_SESSION['UUID']),
            'message' => $profileInfo,
            'message-params' => $profileInfoParams
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
                'Vorname' => array('regex' => '/^[^\\/<>\s]{1,64}$/'),
                'Nachname' => array('regex' => '/^[^\\/<>\s]{1,64}$/'),
                'EMail' => array('regex' => '/^\S{1,64}@\S{1,250}.\S{2,64}$/'),
                'OfflineIM' => array('regex' => '/^(|on)$/'),
                'PartnerName' => array('regex' => '/^[^\\/<>\s]{1,64} [^\\/<>\s]{1,64}$/')
            ));
            
            if ($validator->isValid($_POST)) {
                if(isset($_POST['Vorname'])) {
                    $newFirstName = trim($_POST['Vorname']);
                    
                    if($newFirstName != "" && $_SESSION['FIRSTNAME'] != $newFirstName) {
                        if($this->setNamePart('FirstName', $newFirstName, 'LastName', isset($_POST['Nachname']) && strlen(trim($_POST['Nachname'])) > 0 ? $_POST['Nachname'] : $_SESSION['LASTNAME'])) {
                            $_SESSION['FIRSTNAME'] = $newFirstName;
                            $_SESSION['USERNAME'] = $_SESSION['FIRSTNAME']." ".$_SESSION['LASTNAME'];
                            $_SESSION['DISPLAYNAME'] = strtoupper($_SESSION['USERNAME']);
                        }
                        else {
                            $_SESSION['profile_info'] = 'dashboard.profile.error.nameTaken';
                        }
                    }
                }
            
                if (isset($_POST['Nachname'])) {
                    $newLastName = trim($_POST['Nachname']);
                    
                    if ($newLastName != "" && $_SESSION['LASTNAME'] != $newLastName) {
                        if ($this->setNamePart('LastName', $newLastName, 'FirstName', isset($_POST['Vorname']) && strlen(trim($_POST['Vorname'])) > 0 ? $_POST['Vorname'] : $_SESSION['FIRSTNAME'])) {
                            $_SESSION['LASTNAME'] = $newLastName;
                            $_SESSION['USERNAME'] = $_SESSION['FIRSTNAME']." ".$_SESSION['LASTNAME'];
                            $_SESSION['DISPLAYNAME'] = strtoupper($_SESSION['USERNAME']);
                        } else {
                            $_SESSION['profile_info'] = 'dashboard.profile.error.nameTaken';
                        }
                    }
                }
            
                if (isset($_POST['EMail'])) {
                    $newEmail = trim($_POST['EMail']);
            
                    if ($newEmail != "" && $_SESSION['EMAIL'] != $newEmail) {
                        $statement = $this->app->db()->prepare('UPDATE UserAccounts SET Email = :Email WHERE PrincipalID = :PrincipalID');
                        $statement->execute(['Email' => $newEmail, 'PrincipalID' => $_SESSION['UUID']]);
        
                        $statement = $this->app->db()->prepare('UPDATE usersettings SET email = :Email WHERE useruuid = :PrincipalID');
                        $statement->execute(['Email' => $newEmail, 'PrincipalID' => $_SESSION['UUID']]);
        
                        $_SESSION['EMAIL'] = $newEmail;
                    }
                }
            
                if (isset($_POST['OfflineIM']) && $_POST['OfflineIM'] == "on") {
                    $statement = $this->app->db()->prepare('UPDATE usersettings SET imviaemail = :IMState WHERE useruuid = :PrincipalID');
                    $statement->execute(['IMState' => 'true', 'PrincipalID' => $_SESSION['UUID']]);
                } else {
                    $statement = $this->app->db()->prepare('UPDATE usersettings SET imviaemail = :IMState WHERE useruuid = :PrincipalID');
                    $statement->execute(['IMState' => 'false', 'PrincipalID' => $_SESSION['UUID']]);
                }

                if (isset($_POST['PartnerName']) && $_POST['PartnerName'] != "") {
                    $opensim = new OpenSim($this->app->db());

                    $newPartner = trim($_POST['PartnerName']);
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
                            $_SESSION['profile_info'] = 'dashboard.profile.passwordChanged';
                        } else {
                            $_SESSION['profile_info'] = 'dashboard.profile.error.invalidCredentials';
                        }
                    } else {
                        $_SESSION['profile_info'] = 'register.error.passwordTooShort';
                        $_SESSION['profile_info_params'] = ['length' => $this->app->config('password-min-length')];
                    }
                } else {
                    $_SESSION['profile_info'] = 'dashboard.profile.error.passwordsNotMatching';
                }
            } else {
                $_SESSION['profile_info'] = 'dashboard.profile.error.passwordChangeInvalid';
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
                        $_SESSION['profile_info'] = 'dashboard.profile.delete.error.serverError';
                    }
                }
                else {
                    $_SESSION['profile_info'] = 'dashboard.profile.delete.error.invalidCredentials';
                }
            }
            else {
                $_SESSION['profile_info'] = 'dashboard.profile.delete.error.invalid';
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
