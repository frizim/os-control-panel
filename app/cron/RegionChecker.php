<?php
declare(strict_types=1);

namespace Mcp\Cron;

use Mcp\OpenSim;
use Mcp\Util\Util;

class RegionChecker extends CronJob
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, Frequency::DAILY);
    }

    public function run(): bool
    {
        $statement = $this->app->db()->prepare("SELECT uuid,regionName,owner_uuid,serverURI,OfflineTimer FROM regions LEFT JOIN mcp_regions_info ON regions.uuid = mcp_regions_info.regionID COLLATE utf8mb3_unicode_ci");
        $statement->execute();

        while ($row = $statement->fetch()) {
            $curl = curl_init($row['serverURI'] . 'jsonSimStats');
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);

            if ($result === false || strlen($result) == 0) {
                if ($row['OfflineTimer'] == null) {
                    continue;
                }

                $opensim = new OpenSim($this->app->db());

                $longOffline = ($row['OfflineTimer'] + 3600) <= time();
                echo "Die Region " . $row['regionName'] . " von " . $opensim->getUserName($row['owner_uuid']) . " ist " . ($longOffline ? "seit über einer Stunde" : "") . " nicht erreichbar.\n"; //TODO: Increase to 1-3 months

                if ($longOffline) {
                    if ($this->app->config('grid')['delete-inactive-regions']) {
                        Util::sendInworldIM("00000000-0000-0000-0000-000000000000", $row['owner_uuid'], "Region", $this->app->config('grid')['homeurl'], "WARNUNG: Deine Region '" . $row['regionName'] . "' ist nicht erreichbar und wurde deshalb aus dem Grid entfernt.");

                        $statementUpdate = $this->app->db()->prepare('DELETE FROM regions WHERE uuid = :uuid');
                        $statementUpdate->execute(['uuid' => $row['uuid']]);
                    }
                    $statementRemoveStats = $this->app->db()->prepare('DELETE FROM mcp_regions_info WHERE regionID = :uuid');
                    $statementRemoveStats->execute(['uuid' => $row['uuid']]);
                } elseif ($this->app->config('grid')['warn-inactive-regions']) {
                    Util::sendInworldIM("00000000-0000-0000-0000-000000000000", $row['owner_uuid'], "Region", $this->app->config('grid')['homeurl'], "WARNUNG: Deine Region '" . $row['regionName'] . "' ist nicht erreichbar!");
                }
            } else {
                $regionData = json_decode($result);

                $statementAccounts = $this->app->db()->prepare('REPLACE INTO `mcp_regions_info` (`regionID`, `RegionVersion`, `ProcMem`, `Prims`, `SimFPS`, `PhyFPS`, `OfflineTimer`) VALUES (:regionID, :RegionVersion, :ProcMem, :Prims, :SimFPS, :PhyFPS, :OfflineTimer)');
                $statementAccounts->execute(['regionID' => $row['uuid'], 'RegionVersion' => $regionData->Version, 'ProcMem' => intval(str_replace(',', '', $regionData->ProcMem)), 'Prims' => $regionData->Prims, 'SimFPS' => $regionData->SimFPS, 'PhyFPS' => $regionData->PhyFPS, 'OfflineTimer' => time()]);
            }
        }
        
        return true;
    }
}
