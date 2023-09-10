<?php
declare(strict_types=1);

namespace Mcp;

use Exception;
use PDO;

class MigrationManager
{
    private const INIT = [
        'CREATE TABLE IF NOT EXISTS `mcp_user_identities` (`PrincipalID` CHAR(36) NOT NULL, `IdentityID` CHAR(36) NOT NULL, PRIMARY KEY (`PrincipalID`, `IdentityID`)) ENGINE=MyISAM CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci',
        'CREATE TABLE IF NOT EXISTS `mcp_password_reset` (`PrincipalID` CHAR(36) NOT NULL, `Token` CHAR(32) NOT NULL, `RequestTime` BIGINT NOT NULL, PRIMARY KEY(`PrincipalID`), UNIQUE(`Token`)) ENGINE MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci',
        'CREATE TABLE IF NOT EXISTS `mcp_invites` (`InviteCode` CHAR(64) NOT NULL, PRIMARY KEY (`InviteCode`)) ENGINE InnoDB',
        'CREATE TABLE IF NOT EXISTS `mcp_offlineim_send` (`id` int(6) NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci',
        'CREATE TABLE IF NOT EXISTS `mcp_regions_info` (`regionID` CHAR(36) NOT NULL COLLATE utf8_unicode_ci, `RegionVersion` VARCHAR(128) NOT NULL DEFAULT "" COLLATE utf8_unicode_ci, `ProcMem` INT(11) NOT NULL, `Prims` INT(11) NOT NULL, `SimFPS` INT(11) NOT NULL, `PhyFPS` INT(11) NOT NULL, `OfflineTimer` INT(11) NOT NULL DEFAULT 0, PRIMARY KEY (`regionID`) USING BTREE) COLLATE=utf8_unicode_ci ENGINE=InnoDB',
        'CREATE TABLE IF NOT EXISTS `mcp_cron_runs` (`Name` VARCHAR(50) NOT NULL, `LastRun` INT(11) UNSIGNED NOT NULL, PRIMARY KEY(`Name`)) ENGINE InnoDB',
        'CREATE TABLE IF NOT EXISTS `mcp_iar_state` (`userID` CHAR(36) NOT NULL COLLATE utf8_unicode_ci, `filesize` BIGINT(20) NOT NULL DEFAULT 0, `iarfilename` VARCHAR(64) NOT NULL COLLATE utf8_unicode_ci, `state` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0, `created` INT(11) UNSIGNED NOT NULL DEFAULT 0, PRIMARY KEY (`userID`) USING BTREE) COLLATE=utf8_unicode_ci ENGINE=InnoDB',
        'CREATE TRIGGER IF NOT EXISTS del_id_trig AFTER DELETE ON UserAccounts FOR EACH ROW DELETE FROM mcp_user_identities WHERE mcp_user_identities.PrincipalID = OLD.PrincipalID OR mcp_user_identities.IdentityID = OLD.PrincipalID',
        'CREATE TRIGGER IF NOT EXISTS del_pwres_trig AFTER DELETE ON UserAccounts FOR EACH ROW DELETE FROM mcp_password_reset WHERE mcp_password_reset.PrincipalID = OLD.PrincipalID'
    ];

    private const MIGRATIONS = [
        1 => [
            'RENAME TABLE IF EXISTS UserIdentitys TO mcp_user_identities, PasswordResetTokens TO mcp_password_reset, InviteCodes TO mcp_invites, im_offline_send TO mcp_offlineim_send, regions_info TO mcp_regions_info',
            'ALTER TABLE mcp_invites MODIFY COLUMN InviteCode CHAR(64) NOT NULL',
            'ALTER TABLE mcp_regions_info MODIFY COLUMN regionID CHAR(36), MODIFY COLUMN ProcMem INT(11) UNSIGNED NOT NULL, MODIFY COLUMN Prims INT(11) UNSIGNED NOT NULL, MODIFY COLUMN SimFPS FLOAT NOT NULL, MODIFY COLUMN PhyFPS FLOAT NOT NULL, MODIFY COLUMN OfflineTimer BIGINT UNSIGNED NOT NULL DEFAULT 0',
            'CREATE TRIGGER IF NOT EXISTS del_id_trig AFTER DELETE ON UserAccounts FOR EACH ROW DELETE FROM mcp_user_identities WHERE mcp_user_identities.PrincipalID = OLD.PrincipalID OR mcp_user_identities.IdentityID = OLD.PrincipalID',
            'CREATE TRIGGER IF NOT EXISTS del_pwres_trig AFTER DELETE ON UserAccounts FOR EACH ROW DELETE FROM mcp_password_reset WHERE mcp_password_reset.PrincipalID = OLD.PrincipalID'
        ],
        2 => [
            'CREATE TABLE IF NOT EXISTS `mcp_cron_runs` (`Name` VARCHAR(50) NOT NULL, `LastRun` INT(11) UNSIGNED NOT NULL, PRIMARY KEY(`Name`)) ENGINE InnoDB'
        ],
        3 => [
            'RENAME TABLE IF EXISTS iarstates TO mcp_iar_state',
            'ALTER TABLE mcp_iar_state MODIFY COLUMN userID CHAR(36) NOT NULL COLLATE utf8_unicode_ci, DROP COLUMN running, ADD COLUMN `state` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER iarfilename, ADD COLUMN created INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `state`'
        ]
    ];

    private const MIGRATE_VERSION_CURRENT = 4;

    private int $migrateVersion;
    private string $migrateVersionFile;

    public function __construct(string $migrateVersionFile)
    {
        $this->migrateVersionFile = $migrateVersionFile;
        $this->migrateVersion = 0;
        $apcu = false;
        if ($this->apcuAvailable()) {
            $this->migrateVersion = apcu_exists('mcp_migrate_version') ? apcu_fetch('mcp_migrate_version') : 0;
            $apcu = true;
        }
        
        if ($this->migrateVersion == 0 && file_exists($migrateVersionFile)) {
            $migrateVer = file_get_contents($migrateVersionFile);
            if (preg_match('/^\d+$/', $migrateVer)) {
                $this->migrateVersion = intval($migrateVer);
                if ($apcu) {
                    apcu_store('mcp_migrate_version', $this->migrateVersion);
                }
            }
        }
    }

    public function isMigrated(): bool
    {
        return $this->migrateVersion == $this::MIGRATE_VERSION_CURRENT;
    }

    public function migrate(PDO $db): bool
    {
        if ($this->migrateVersion == 0) {
            // MCP < v2.x.x might be initialized without migration data
            $checkLegacy = $db->prepare('SHOW TABLES LIKE ?');
            $checkLegacy->execute(['UserIdentitys']);
            if ($checkLegacy->rowCount() > 0) {
                $this->migrateVersion = 1;
            }
            else {
                return $this->runStatements($db, $this::INIT) && $this->updateVersion();
            }
        }

        for ($ver = $this->migrateVersion; $ver <= count($this::MIGRATIONS); $ver++) {
            if (!isset($this::MIGRATIONS[$ver])) {
                break;
            }

            if (!$this->runStatements($db, $this::MIGRATIONS[$ver])) {
                return false;
            }
        }

        $this->updateVersion();
        return true;
    }

    private function updateVersion(): bool
    {
        file_put_contents($this->migrateVersionFile, $this::MIGRATE_VERSION_CURRENT);
        if ($this->apcuAvailable()) {
            apcu_store('mcp_migrate_version', $this::MIGRATE_VERSION_CURRENT);
        }
        return true;
    }

    private function runStatements(PDO $db, array $stmts): bool
    {
        try {
            foreach ($stmts as $stmt) {
                $db->exec($stmt);
            }
            return true;
        } catch (Exception $e) {
            error_log('Could not execute statements: '.$e->getMessage()."\n".$e->getTraceAsString());
            return false;
        }
    }

    private function apcuAvailable(): bool
    {
        return function_exists('apcu_fetch') && apcu_enabled();
    }
}
