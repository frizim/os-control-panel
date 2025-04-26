<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\OpenSim;
use Mcp\RequestHandler;
use Mcp\Middleware\PreSessionMiddleware;

use Exception;
use Mcp\Util\Util;

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
                $this->displayPage('register.error.tos');
            } else {
                $this->displayPage('register.error.invalid');
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
                $this->displayPage('register.error.nameTaken');
                return;
            }
        }
    
        $pass = trim($_POST['password']);
        if (strlen($pass) < $this->app->config('password-min-length')) {
            $this->displayPage('register.error.passwordTooShort', ['length' => $this->app->config('password-min-length')]);
            return;
        }
    
        $email = trim($_POST['email']);
    
        $avatar = null;
        if (isset($this->app->config('default-avatar')[$_POST['avatar']])) {
            $avatar = trim($_POST['avatar']);
        } else {
            $this->displayPage('register.error.invalidAvatar');
            return;
        }
    
        $opensim = new OpenSim($this->app->db());
    
        $avatarUUID = $opensim->generateUuid();
        $salt = bin2hex(random_bytes(16));
        $passwordHash = md5(md5($pass).':'.$salt);
    
        $statementInviteDeleter = $this->app->db()->prepare('DELETE FROM mcp_invites WHERE InviteCode = :code');
        $statementInviteDeleter->execute(['code' => $_REQUEST['code']]);
        if ($statementInviteDeleter->rowCount() == 0) {
            Util::displayError($this->app, 'register.error.inviteExpired');
            return;
        }
    
        try {
            $this->app->db()->beginTransaction();
    
            $statementAuth = $this->app->db()->prepare('INSERT INTO `auth` (`UUID`, `passwordHash`, `passwordSalt`, `webLoginKey`, `accountType`) VALUES (:UUID, :HASHVALUE, :SALT, :WEBKEY, :ACCTYPE)');
            $statementAuth->execute(['UUID' => $avatarUUID, 'HASHVALUE' => $passwordHash, 'SALT' => $salt, 'WEBKEY' => "00000000-0000-0000-0000-000000000000", 'ACCTYPE' => "UserAccount"]);
    
            $statementAccounts = $this->app->db()->prepare('INSERT INTO `UserAccounts` (`PrincipalID`, `ScopeID`, `FirstName`, `LastName`, `Email`, `ServiceURLs`, `Created`, `UserLevel`, `UserFlags`, `UserTitle`, `active`) VALUES (:PrincipalID, :ScopeID, :FirstName, :LastName, :Email, :ServiceURLs, :Created, :UserLevel, :UserFlags, :UserTitle, :active )');
            $statementAccounts->execute(['PrincipalID' => $avatarUUID, 'ScopeID' => "00000000-0000-0000-0000-000000000000", 'FirstName' => $nameParts[0], 'LastName' => $nameParts[1], 'Email' => $email, 'ServiceURLs' => "HomeURI= GatekeeperURI= InventoryServerURI= AssetServerURI= ", 'Created' => time(), 'UserLevel' => 0, 'UserFlags' => 0, 'UserTitle' => "", 'active' => 1]);
    
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
            $this->displayPage('register.error.serverError');
            return;
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

    private function displayPage(string $message = '', array $params = []): void
    {
        $this->app->template('register.php')->parent('__presession.php')->vars([
            'title' => 'Registrieren',
            'message' => $message,
            'message-params' => $params,
            'tos-url' => $this->app->config('tos-url'),
            'invcode' => $_REQUEST['code'],
            'avatars' => $this->app->config('default-avatar'),
            'pwMinLength' => $this->app->config('password-min-length')
        ])->render();
    }

    private function checkInvite(): bool
    {
        if (!isset($_REQUEST['code'])) {
            Util::displayError($this->app, 'register.error.noInvite');
        } elseif (strlen($_REQUEST['code']) != 32 || !preg_match('/^[a-f0-9]+$/', $_REQUEST['code'])) {
            Util::displayError($this->app, 'register.error.invalidInvite');
        } else {
            $statementInviteCode = $this->app->db()->prepare("SELECT 1 FROM mcp_invites WHERE InviteCode = ? LIMIT 1");
            $statementInviteCode->execute([$_REQUEST['code']]);
        
            if ($statementInviteCode->rowCount() == 0) {
                Util::displayError($this->app, 'register.error.invalidInvite');
                return false;
            }
            return true;
        }

        return false;
    }
}
