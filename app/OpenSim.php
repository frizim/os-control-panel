<?php
declare(strict_types=1);

namespace Mcp;

use Exception;
use PDO;

class OpenSim
{

    private PDO $pdo;
    private bool $apcu;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->apcu = function_exists('apcu_fetch') && apcu_enabled();
    }

    private function getUserNameFromGridData($userID, $table, $row): ?string
    {
        $statementGridUser = $this->pdo->prepare('SELECT '.$row.' FROM '.$table.' WHERE '.$row.' LIKE ?');
        $statementGridUser->execute(array($userID.';%'));

        while ($rowGridUser = $statementGridUser->fetch()) {
            $userData = explode(";", $rowGridUser[$row]);

            if (count($userData) >= 3) {
                $dbUserID = $userData[0];
                $dbUserName = $userData[2];
                
                if ($dbUserID == $userID) {
                    if ($this->apcu) {
                        apcu_store('os_username_'.$userID, $dbUserName, 600);
                    }
                    return $dbUserName;
                }
            }
        }

        return null;
    }

    public function getUserName($userID): string
    {
        if ($userID == "00000000-0000-0000-0000-000000000000") {
            return "Unknown User";
        }

        if ($this->apcu && apcu_exists('os_username_'.$userID)) {
            return apcu_fetch('os_username_'.$userID);
        }

        $statementUser = $this->pdo->prepare('SELECT FirstName,LastName FROM UserAccounts WHERE PrincipalID = ?');
        $statementUser->execute(array($userID));

        if ($rowUser = $statementUser->fetch()) {
            $name = $rowUser['FirstName'].' '.$rowUser['LastName'];
            if ($this->apcu) {
                apcu_store('os_username_'.$userID, $name, 300);
            }
            
            return $name;
        }

        $res = $this->getUserNameFromGridData($userID, 'GridUser', 'UserID');
        if ($res == null) {
            $res = $this->getUserNameFromGridData($userID, 'Friends', 'PrincipalID');
        }

        return $res == null ? "Unknown User" : $res;
    }

    public function getUserUUID($userName): ?string
    {
        $statementUser = $this->pdo->prepare('SELECT PrincipalID,FirstName,LastName FROM UserAccounts WHERE FirstName = ? AND LastName = ?');
        $statementUser->execute(explode(' ', $userName));

        if ($rowUser = $statementUser->fetch()) {
            return $rowUser['PrincipalID'];
        }

        return null;
    }

    public function getRegionName($regionID): string
    {
        $statementRegion = $this->pdo->prepare("SELECT regionName FROM regions WHERE uuid = ?");
        $statementRegion->execute(array($regionID));

        if ($rowRegion = $statementRegion->fetch()) {
            return $rowRegion['regionName'];
        }

        return "Unknown Region";
    }

    public function getPartner($userID): string
    {
        $statement = $this->pdo->prepare("SELECT profilePartner FROM userprofile WHERE useruuid = ?");
        $statement->execute(array($userID));

        while ($row = $statement->fetch()) {
            if ($row['profilePartner'] != "00000000-0000-0000-0000-000000000000") {
                return $row['profilePartner'];
            }
        }

        return '';
    }

    public function allowOfflineIM($userID): string
    {
        $statement = $this->pdo->prepare("SELECT imviaemail FROM usersettings WHERE useruuid = ?");
        $statement->execute(array($userID));

        if ($row = $statement->fetch()) {
            return strtoupper($row['imviaemail']);
        }

        return "FALSE";
    }

    public function getUserMail($userID): string
    {
        $statement = $this->pdo->prepare("SELECT Email FROM UserAccounts WHERE PrincipalID = ?");
        $statement->execute(array($userID));

        if ($row = $statement->fetch()) {
            return $row['Email'];
        }

        return "";
    }

    private function getEntryCount($table): int
    {
        $statementCount = $this->pdo->prepare('SELECT COUNT(*) AS Count FROM '.$table);
        $statementCount->execute();
        if ($row = $statementCount->fetch()) {
            return $row['Count'];
        }
        return 0;
    }

    public function getUserCount(): int
    {
        return $this->getEntryCount('UserAccounts');
    }

    public function getRegionCount(): int
    {
        return $this->getEntryCount('regions');
    }

    public function getOnlineCount(): int
    {
        return $this->getEntryCount('Presence');
    }

    public function deleteUser($uuid): bool
    {
        try {
            $this->pdo->beginTransaction();

            $statementAuth = $this->pdo->prepare('DELETE FROM auth WHERE UUID = ?');
            $statementAuth->execute([$uuid]);

            $statementAgentPrefs = $this->pdo->prepare('DELETE FROM AgentPrefs WHERE PrincipalID = ?');
            $statementAgentPrefs->execute([$uuid]);

            $statementAvatars = $this->pdo->prepare('DELETE FROM Avatars WHERE PrincipalID = ?');
            $statementAvatars->execute([$uuid]);

            $statementGridUser = $this->pdo->prepare('DELETE FROM GridUser WHERE UserID = ?');
            $statementGridUser->execute([$uuid]);

            $statementEstateUser = $this->pdo->prepare('DELETE FROM estate_users WHERE uuid = ?');
            $statementEstateUser->execute([$uuid]);

            $statementEstateBan = $this->pdo->prepare('DELETE FROM estateban WHERE bannedUUID = ?');
            $statementEstateBan->execute([$uuid]);

            $statementHgTraveling = $this->pdo->prepare('DELETE FROM hg_traveling_data WHERE UserID = ?');
            $statementHgTraveling->execute([$uuid]);

            $statementUserIdentitys = $this->pdo->prepare('DELETE FROM mcp_user_identities WHERE PrincipalID = ?');
            $statementUserIdentitys->execute([$uuid]);

            $statementFriends = $this->pdo->prepare('DELETE FROM Friends WHERE PrincipalID = ? OR Friend = ?');
            $statementFriends->execute([$uuid, $uuid]);

            $statementImOffline = $this->pdo->prepare('DELETE FROM im_offline WHERE PrincipalID = ?');
            $statementImOffline->execute([$uuid]);

            $statementInventoryFolders = $this->pdo->prepare('DELETE FROM inventoryfolders WHERE agentID = ?');
            $statementInventoryFolders->execute([$uuid]);

            $statementInventoryItems = $this->pdo->prepare('DELETE FROM inventoryitems WHERE avatarID = ?');
            $statementInventoryItems->execute([$uuid]);

            $statementGroupMembership = $this->pdo->prepare('DELETE FROM os_groups_membership WHERE PrincipalID = ?');
            $statementGroupMembership->execute([$uuid]);

            $statementGroupRoles = $this->pdo->prepare('DELETE FROM os_groups_rolemembership WHERE PrincipalID = ?');
            $statementGroupRoles->execute([$uuid]);

            $statementGroupRoles = $this->pdo->prepare('DELETE FROM Presence WHERE UserID = ?');
            $statementGroupRoles->execute([$uuid]);

            $statementMute = $this->pdo->prepare('DELETE FROM MuteList WHERE AgentID = ? OR MuteID = ?');
            $statementMute->execute([$uuid, $uuid]);

            $statementUserAccounts = $this->pdo->prepare('DELETE FROM UserAccounts WHERE PrincipalID = ?');
            $statementUserAccounts->execute([$uuid]);

            $statementUserData = $this->pdo->prepare('DELETE FROM userdata WHERE UserId = ?');
            $statementUserData->execute([$uuid]);

            $statementUserNotes = $this->pdo->prepare('DELETE FROM usernotes WHERE targetuuid = ?');
            $statementUserNotes->execute([$uuid]);

            $statementUserProfile = $this->pdo->prepare('DELETE FROM userprofile WHERE useruuid = ?');
            $statementUserProfile->execute([$uuid]);

            $statementUserSettings = $this->pdo->prepare('DELETE FROM usersettings WHERE useruuid = ?');
            $statementUserSettings->execute([$uuid]);

            $this->pdo->commit();

            return true;
        } catch (Exception $pdoException) {
            $this->pdo->rollBack();
            error_log('Could not delete account '.$uuid.': '.$pdoException->getMessage());
            return false;
        }
    }

    public function deleteIdentity($uuid, $identId): bool
    {
        $statementValidate = $this->pdo->prepare('SELECT 1 FROM mcp_user_identities WHERE PrincipalID = ? AND IdentityID = ?');
        $statementValidate->execute([$uuid, $identId]);

        if($statementValidate->fetch()) {
            $statementDelete = $this->pdo->prepare('DELETE FROM UserAccounts WHERE PrincipalID = ?');
            $statementDelete->execute([$identId]);

            $statementUserProfile = $this->pdo->prepare('DELETE FROM userprofile WHERE useruuid = ?');
            $statementUserProfile->execute([$identId]);

            return true;
        }

        return false;
    }

    public function generateUuid(): string
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
    
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
    
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
}
