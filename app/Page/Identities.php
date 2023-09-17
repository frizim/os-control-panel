<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\OpenSim;
use Mcp\Middleware\LoginRequiredMiddleware;
use Mcp\Util\TemplateVarArray;

class Identities extends \Mcp\RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new LoginRequiredMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {
        $statementCheckForEntry = $this->app->db()->prepare("SELECT 1 FROM mcp_user_identities WHERE PrincipalID = ? LIMIT 1");
        $statementCheckForEntry->execute(array($_SESSION['UUID']));
    
        if ($statementCheckForEntry->rowCount() == 0) {
            $statement = $this->app->db()->prepare('INSERT INTO `mcp_user_identities` (PrincipalID, IdentityID) VALUES (:PrincipalID, :IdentityID)');
            $statement->execute(['PrincipalID' => $_SESSION['UUID'], 'IdentityID' => $_SESSION['UUID']]);
        }
    
        $statement = $this->app->db()->prepare("SELECT IdentityID FROM mcp_user_identities WHERE PrincipalID = ? ORDER BY IdentityID ASC");
        $statement->execute(array($_SESSION['UUID']));
    
        $opensim = new OpenSim($this->app->db());
        $res = new TemplateVarArray();
    
        while ($row = $statement->fetch()) {
            $ident = new TemplateVarArray();
            $ident["uuid"] = $row["IdentityID"];
            $ident["name"] = trim($opensim->getUserName($row['IdentityID']));
            $ident["active"] = $row['IdentityID'] == $_SESSION['UUID'];
            $res[] = $ident;
        }
    
        $message = '';
        if (isset($_SESSION['identities_err'])) {
            $message = '<div class="alert alert-danger" role="alert">'.$_SESSION['identities_err'].'</div>';
            unset($_SESSION['identities_err']);
        }
        
        $this->app->template('identities.php')->parent('__dashboard.php')->vars([
            'title' => 'Identitäten',
            'username' => $_SESSION['DISPLAYNAME'],
            'activeIdent' => $_SESSION['FIRSTNAME'].' '.$_SESSION['LASTNAME'],
            'activeUuid' => $_SESSION['UUID'],
            'message' => $message,
            'identities' => &$res
        ])->render();
    }

    public function post(): void
    {
        if (isset($_POST['enableIdent'])) {
            $validator = new FormValidator(array(
                'uuid' => array('required' => true, 'regex' => '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/')
            ));

            if ($validator->isValid($_POST)) {
                $statement = $this->app->db()->prepare("SELECT 1 FROM mcp_user_identities WHERE PrincipalID = :PrincipalID AND IdentityID = :IdentityID LIMIT 1");
                $statement->execute(['PrincipalID' => $_SESSION['UUID'], 'IdentityID' => $_POST['uuid']]);
        
                $statementPresence = $this->app->db()->prepare("SELECT 1 FROM Presence WHERE UserID = :PrincipalID LIMIT 1");
                $statementPresence->execute(['PrincipalID' => $_SESSION['UUID']]);
        
                if ($statementPresence->rowCount() == 0) {
                    if ($statement->rowCount() == 1) {
                        $statementAuth = $this->app->db()->prepare('UPDATE auth SET UUID = :IdentityID WHERE UUID = :PrincipalID');
                        $statementAuth->execute(['IdentityID' => $_POST['uuid'], 'PrincipalID' => $_SESSION['UUID']]);
        
                        $statementUserIdentitys = $this->app->db()->prepare('UPDATE mcp_user_identities SET PrincipalID = :IdentityID WHERE PrincipalID = :PrincipalID');
                        $statementUserIdentitys->execute(['IdentityID' => $_POST['uuid'], 'PrincipalID' => $_SESSION['UUID']]);
        
                        $statementFriends = $this->app->db()->prepare('UPDATE Friends SET PrincipalID = :IdentityID WHERE PrincipalID = :PrincipalID');
                        $statementFriends->execute(['IdentityID' => $_POST['uuid'], 'PrincipalID' => $_SESSION['UUID']]);
        
                        //$statementReFriends = $this->app->db()->prepare('UPDATE Friends SET Friend = :IdentityID WHERE Friend = :PrincipalID');
                        //$statementReFriends->execute(['IdentityID' => $_POST['uuid'], 'PrincipalID' => $_SESSION['UUID']]);
        
                        $statementInventoryFolders = $this->app->db()->prepare('UPDATE inventoryfolders SET agentID = :IdentityID WHERE agentID = :PrincipalID AND type != :InventarTyp');
                        $statementInventoryFolders->execute(['IdentityID' => $_POST['uuid'], 'PrincipalID' => $_SESSION['UUID'], 'InventarTyp' => 46]);
        
                        $statementInventoryItems = $this->app->db()->prepare('UPDATE inventoryitems SET avatarID = :IdentityID WHERE avatarID = :PrincipalID');
                        $statementInventoryItems->execute(['IdentityID' => $_POST['uuid'], 'PrincipalID' => $_SESSION['UUID']]);
        
                        $statementGroupMembership = $this->app->db()->prepare('UPDATE os_groups_membership SET PrincipalID = :IdentityID WHERE PrincipalID = :PrincipalID');
                        $statementGroupMembership->execute(['IdentityID' => $_POST['uuid'], 'PrincipalID' => $_SESSION['UUID']]);
        
                        $statementGroupRoles = $this->app->db()->prepare('UPDATE os_groups_rolemembership SET PrincipalID = :IdentityID WHERE PrincipalID = :PrincipalID');
                        $statementGroupRoles->execute(['IdentityID' => $_POST['uuid'], 'PrincipalID' => $_SESSION['UUID']]);
        
                        $statementGroupRoles = $this->app->db()->prepare('DELETE FROM Presence WHERE UserID = :PrincipalID');
                        $statementGroupRoles->execute(['PrincipalID' => $_SESSION['UUID']]);
        
                        $_SESSION['LOGIN'] = 'false';
                        session_destroy();
                    }
                } else {
                    $_SESSION['identities_err'] = 'Du kannst die Identität nicht ändern, während du angemeldet bist. Bitte schließe den Viewer.';
                }
            }
        } elseif (isset($_POST['createIdent'])) {
            $validator = new FormValidator(array(
                'newName' => array('required' => true, 'regex' => '/^[^\\/<>\s]{1,64} [^\\/<>\s]{1,64}$/')
            ));

            if ($validator->isValid($_POST)) {
                $avatarNameParts = explode(" ", trim($_POST['newName']));

                if (count($avatarNameParts) == 2) {
                    $statement = $this->app->db()->prepare("SELECT 1 FROM UserAccounts WHERE FirstName = :FirstName AND LastName = :LastName LIMIT 1");
                    $statement->execute(['FirstName' => trim($avatarNameParts[0]), 'LastName' => trim($avatarNameParts[1])]);
        
                    if ($statement->rowCount() == 0) {
                        $avatarUUID = (new OpenSim($this->app->db()))->generateUuid();
        
                        $statementAccounts = $this->app->db()->prepare('INSERT INTO UserAccounts (PrincipalID, ScopeID, FirstName, LastName, Email, ServiceURLs, Created, UserLevel, UserFlags, UserTitle, active) VALUES (:PrincipalID, :ScopeID, :FirstName, :LastName, :Email, :ServiceURLs, :Created, :UserLevel, :UserFlags, :UserTitle, :active )');
                        $statementAccounts->execute(['PrincipalID' => $avatarUUID, 'ScopeID' => "00000000-0000-0000-0000-000000000000", 'FirstName' => $avatarNameParts[0], 'LastName' => $avatarNameParts[1], 'Email' => $_SESSION['EMAIL'], 'ServiceURLs' => "HomeURI= GatekeeperURI= InventoryServerURI= AssetServerURI= ", 'Created' => time(), 'UserLevel' => 0, 'UserFlags' => 0, 'UserTitle' => "", 'active' => 1]);

                        $statementProfile = $this->app->db()->prepare('INSERT INTO `userprofile` (`useruuid`, `profilePartner`, `profileImage`, `profileURL`, `profileFirstImage`, `profileAllowPublish`, `profileMaturePublish`, `profileWantToMask`, `profileWantToText`, `profileSkillsMask`, `profileSkillsText`, `profileLanguages`, `profileAboutText`, `profileFirstText`) VALUES (:useruuid, :profilePartner, :profileImage, :profileURL, :profileFirstImage, :profileAllowPublish, :profileMaturePublish, :profileWantToMask, :profileWantToText, :profileSkillsMask, :profileSkillsText, :profileLanguages, :profileAboutText, :profileFirstText)');
                        $statementProfile->execute(['useruuid' => $avatarUUID, 'profilePartner' => "00000000-0000-0000-0000-000000000000", 'profileImage' => "00000000-0000-0000-0000-000000000000", 'profileURL' => '', 'profileFirstImage' => "00000000-0000-0000-0000-000000000000", "profileAllowPublish" => "0", "profileMaturePublish" => "0", "profileWantToMask" => "0", "profileWantToText" => "", "profileSkillsMask" => "0", "profileSkillsText" => "", "profileLanguages" => "", "profileAboutText" => "", "profileFirstText" => ""]);
                        
                        $statementUserIdentitys = $this->app->db()->prepare('INSERT INTO mcp_user_identities (PrincipalID, IdentityID) VALUES (:PrincipalID, :IdentityID)');
                        $statementUserIdentitys->execute(['PrincipalID' => $_SESSION['UUID'], 'IdentityID' => $avatarUUID]);
                    } else {
                        $_SESSION['identities_err'] = 'Dieser Name ist schon in Benutzung.';
                    }
                } else {
                    $_SESSION['identities_err'] = 'Der Name muss aus einem Vor- und einem Nachnamen bestehen.';
                }
            }
        }
        elseif (isset($_POST['deleteIdent'])) {
            $validator = new FormValidator(array(
                'uuid' => array('required' => true, 'regex' => '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/')
            ));

            if ($validator->isValid($_POST)) {
                (new OpenSim($this->app->db()))->deleteIdentity($_SESSION['UUID'], $_POST['uuid']);
            }
        }

        header('Location: index.php?page=identities');
    }
}
