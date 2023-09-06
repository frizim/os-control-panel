<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\OpenSim;
use Mcp\RequestHandler;
use Mcp\Middleware\PreSessionMiddleware;

use Exception;

class Register extends RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new PreSessionMiddleware($app->config('domain')));
    }

    public function get(): void
    {
        if ($this->checkInvite()) {
            $this->displayPage();
        }
    }

    public function post(): void
    {
        $validator = new FormValidator(array(
            'tos' => array('required' => true, 'equals' => 'on'),
            'username' => array('required' => true, 'regex' => '/^[^\\/<>\s]{1,64}( [^\\/<>\s]{1,64})?$/'),
            'password' => array('required' => true, 'regex' => '/^.{1,1000}$/'),
            'email' => array('required' => true, 'regex' => '/^\S{1,64}@\S{1,250}.\S{2,64}$/'),
            'avatar' => array('required' => true)
        ));
    
        if (!$validator->isValid($_POST)) {
            if (!isset($_POST['tos']) || $_POST['tos'] !== true) {
                $this->displayPage("Du musst die Nutzungsbedingungen lesen und Akzeptieren.");
            } else {
                $this->displayPage("Ups da stimmt was nicht. Versuche es bitte noch mal.");
            }
    
            return;
        }
    
        $name = trim($_POST['username']);
        $nameParts = explode(" ", $name);
        if ($name != "") {
            if (count($nameParts) == 1) {
                $name .= " Resident";
                $nameParts = explode(" ", $name);
            }
            
            $statementAvatarName = $this->app->db()->prepare("SELECT 1 FROM UserAccounts WHERE FirstName = :FirstName AND LastName = :LastName LIMIT 1");
            $statementAvatarName->execute(['FirstName' => $nameParts[0], 'LastName' => $nameParts[1]]);
            if ($statementAvatarName->rowCount() > 0) {
                $this->displayPage("Der gewählte Name ist bereits vergeben.");
            }
        }
    
        $pass = trim($_POST['password']);
        if (strlen($pass) < $this->app->config('password-min-length')) {
            $this->displayPage('Dein Passwort muss mindestens '.$this->app->config('password-min-length').' Zeichen lang sein.');
        }
    
        $email = trim($_POST['email']);
    
        $avatar = null;
        if (isset($this->app->config('default-avatar')[$_POST['avatar']])) {
            $avatar = trim($_POST['avatar']);
        } else {
            $this->displayPage("Der gewählte Standardavatar existiert nicht.");
        }
    
        $opensim = new OpenSim($this->app->db());
    
        $avatarUUID = $opensim->generateUuid();
        $salt = bin2hex(random_bytes(16));
        $passwordHash = md5(md5($pass).':'.$salt);
    
        $statementInviteDeleter = $this->app->db()->prepare('DELETE FROM InviteCodes WHERE InviteCode = :code');
        $statementInviteDeleter->execute(['code' => $_REQUEST['code']]);
        if ($statementInviteDeleter->rowCount() == 0) {
            $this->displayError("Der angegebene Einladungscode ist nicht mehr gültig.");
        }
    
        try {
            $this->app->db()->beginTransaction();
    
            $statementAuth = $this->app->db()->prepare('INSERT INTO `auth` (`UUID`, `passwordHash`, `passwordSalt`, `webLoginKey`, `accountType`) VALUES (:UUID, :HASHVALUE, :SALT, :WEBKEY, :ACCTYPE)');
            $statementAuth->execute(['UUID' => $avatarUUID, 'HASHVALUE' => $passwordHash, 'SALT' => $salt, 'WEBKEY' => "00000000-0000-0000-0000-000000000000", 'ACCTYPE' => "UserAccount"]);
    
            $statementAccounts = $this->app->db()->prepare('INSERT INTO `UserAccounts` (`PrincipalID`, `ScopeID`, `FirstName`, `LastName`, `Email`, `ServiceURLs`, `Created`, `UserLevel`, `UserFlags`, `UserTitle`, `active`) VALUES (:PrincipalID, :ScopeID, :FirstName, :LastName, :Email, :ServiceURLs, :Created, :UserLevel, :UserFlags, :UserTitle, :active )');
            $statementAccounts->execute(['PrincipalID' => $avatarUUID, 'ScopeID' => "00000000-0000-0000-0000-000000000000", 'FirstName' => $nameParts[0], 'LastName' => $nameParts[1], 'Email' => $email, 'ServiceURLs' => "HomeURI= GatekeeperURI= InventoryServerURI= AssetServerURI= ", 'Created' => time(), 'UserLevel' => 0, 'UserFlags' => 0, 'UserTitle' => "", 'active' => 1]);
    
            $statementProfile = $this->app->db()->prepare('INSERT INTO `userprofile` (`useruuid`, `profilePartner`, `profileImage`, `profileURL`, `profileFirstImage`, `profileAllowPublish`, `profileMaturePublish`, `profileWantToMask`, `profileWantToText`, `profileSkillsMask`, `profileSkillsText`, `profileLanguages`, `profileAboutText`, `profileFirstText`) VALUES (:useruuid, :profilePartner, :profileImage, :profileURL, :profileFirstImage, :profileAllowPublish, :profileMaturePublish, :profileWantToMask, :profileWantToText, :profileSkillsMask, :profileSkillsText, :profileLanguages, :profileAboutText, :profileFirstText)');
            $statementProfile->execute(['useruuid' => $avatarUUID, 'profilePartner' => "00000000-0000-0000-0000-000000000000", 'profileImage' => "00000000-0000-0000-0000-000000000000", 'profileURL' => '', 'profileFirstImage' => "00000000-0000-0000-0000-000000000000", "profileAllowPublish" => "0", "profileMaturePublish" => "0", "profileWantToMask" => "0", "profileWantToText" => "", "profileSkillsMask" => "0", "profileSkillsText" => "", "profileLanguages" => "", "profileAboutText" => "", "profileFirstText" => ""]);
    
            $statementInventoryFolder = $this->app->db()->prepare('INSERT INTO `inventoryfolders` (`folderName`, `type`, `version`, `folderID`, `agentID`, `parentFolderID`) VALUES (:folderName, :folderTyp, :folderVersion, :folderID, :agentID, :parentFolderID)');
            $inventory = array('Calling Cards' => 2, 'Objects' => 6, 'Landmarks' => 3, 'Clothing' => 5, 'Gestures' => 21, 'Body Parts' => 13, 'Textures' =>  0, 'Scripts' => 10, 'Photo Album' => 15, 'Lost And Found' => 16, 'Trash' => 14, 'Notecards' =>  7, 'My Inventory' =>  8, 'Sounds' =>  1, 'Animations' => 20);
            $inventoryRootFolder = $opensim->generateUuid();
            foreach ($inventory as $folderName => $inventoryType) {
                $folderUUID = $opensim->generateUuid();
                if ($inventoryType == 8) {
                    $folderUUID = $inventoryRootFolder;
                    $folderParent = "00000000-0000-0000-0000-000000000000";
                } else {
                    $folderParent = $inventoryRootFolder;
                }
                $statementInventoryFolder->execute(['agentID' => $avatarUUID, 'folderName' => $folderName, 'folderTyp' => $inventoryType, 'folderVersion' => 1, 'folderID' => $folderUUID, 'parentFolderID' => $folderParent]);
            }
    
            $this->app->db()->commit();
        } catch (Exception $pdoException) {
            $this->app->db()->rollBack();
            error_log('Could not create Account: '.$pdoException->getMessage());
            $this->displayPage('Fehler bei der Erstellung deines Accounts. Bitte versuche es später erneut.');
        }
    
        session_abort();
        session_set_cookie_params([
            'lifetime' => 86400,
            'path' => '/',
            'domain' => $this->app->config('domain'),
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict'
        ]);
        session_start();
        session_regenerate_id(true);
        $_SESSION['FIRSTNAME'] = trim($nameParts[0]);
        $_SESSION['LASTNAME'] = trim($nameParts[1]);
        $_SESSION['EMAIL'] = $email;
        $_SESSION['PASSWORD'] = $passwordHash;
        $_SESSION['SALT'] = $salt;
        $_SESSION['UUID'] = $avatarUUID;
        $_SESSION['LEVEL'] = 0;
        $_SESSION['DISPLAYNAME'] = strtoupper($name);
        $_SESSION['LOGIN'] = 'true';
    
        header('Location: index.php?page=dashboard');
    }

    private function displayPage(string $message = ''): void
    {
        $this->app->template('register.php')->parent('__presession.php')->vars([
            'title' => 'Registrieren',
            'message' => $message,
            'tos-url' => $this->app->config('tos-url'),
            'invcode' => $_REQUEST['code']
        ])->render();
    }

    private function displayError(string $message): void
    {
        $this->app->template('error.php')->parent('__presession.php')->vars([
            'error-message' => $message,
            'title' => 'Fehler'
        ])->render();
    }

    private function checkInvite(): bool
    {
        if (!isset($_REQUEST['code'])) {
            $this->displayError("Du benötigst einen Einladungscode, um dich bei 4Creative zu registrieren.");
        } elseif (strlen($_REQUEST['code']) != 32 || !preg_match('/^[a-f0-9]+$/', $_REQUEST['code'])) {
            $this->displayError("Der angegebene Einladungscode ist nicht gültig. Nutze genau den Link, der dir zugeschickt wurde.");
        } else {
            $statementInviteCode = $this->app->db()->prepare("SELECT 1 FROM InviteCodes WHERE InviteCode = ? LIMIT 1");
            $statementInviteCode->execute([$_REQUEST['code']]);
        
            if ($statementInviteCode->rowCount() == 0) {
                $this->displayError("Der angegebene Einladungscode ist nicht gültig. Nutze genau den Link, der dir zugeschickt wurde.");
                return false;
            }
            return true;
        }

        return false;
    }
}
