<?php
    $createStatement = $RUNTIME['PDO']->prepare("CREATE TABLE IF NOT EXISTS `regions_info` (`regionID` VARCHAR(36) NOT NULL COLLATE 'utf8_unicode_ci', `RegionVersion` VARCHAR(128) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci', `ProcMem` INT(11) NOT NULL, `Prims` INT(11) NOT NULL, `SimFPS` INT(11) NOT NULL, `PhyFPS` INT(11) NOT NULL, `OfflineTimer` INT(11) NOT NULL DEFAULT '0', PRIMARY KEY (`regionID`) USING BTREE) COLLATE='utf8_unicode_ci' ENGINE=InnoDB;");
    $createStatement->execute(); 

    $statement = $RUNTIME['PDO']->prepare("SELECT uuid,regionName,owner_uuid,serverURI FROM regions");
    $statement->execute(); 

    ini_set('default_socket_timeout', 3);

    $ctx = stream_context_create(array('http'=>
        array(
            'timeout' => 3,
        )
    ));

    while($row = $statement->fetch())
    {
        $result = file_get_contents($row['serverURI']."jsonSimStats", false, $ctx);

        if($result == FALSE || $result == "")
        {
            include 'app/OpenSim.php';

            echo "Die Region ".$row['regionName']." von ".$opensim->getUserName($row['owner_uuid'])." ist nicht erreichbar.\n";

            $infoStatement = $RUNTIME['PDO']->prepare("SELECT OfflineTimer FROM regions_info WHERE regionID = :regionID");
            $infoStatement->execute(['regionID' => $row['uuid']]);
            
            if($infoRow = $infoStatement->fetch())
            {
                if(($infoRow['OfflineTimer'] + 3600) <= time())
                {
                    echo "Die Region ".$row['regionName']." von ".$opensim->getUserName($row['owner_uuid'])." ist seit über eine Stunde nicht erreichbar!\n";

                    //sendInworldIM("00000000-0000-0000-0000-000000000000", $row['owner_uuid'], "Region", $RUNTIME['GRID']['HOMEURL'], "WARNUNG: Deine Region '".$row['regionName']."' ist nicht erreichbar und wurde deshalb aus dem Grid entfernt."); 

                    //$statementUpdate = $RUNTIME['PDO']->prepare('DELETE FROM regions WHERE uuid = :uuid');
                    //$statementUpdate->execute(['uuid' => $row['uuid']]);
                }else{
                    //sendInworldIM("00000000-0000-0000-0000-000000000000", $row['owner_uuid'], "Region", $RUNTIME['GRID']['HOMEURL'], "WARNUNG: Deine Region '".$row['regionName']."' ist nicht erreichbar!"); 
                }
            }
        }else{
            $regionData = json_decode($result);

            $statementAccounts = $RUNTIME['PDO']->prepare('REPLACE INTO `regions_info` (`regionID`, `RegionVersion`, `ProcMem`, `Prims`, `SimFPS`, `PhyFPS`, `OfflineTimer`) VALUES (:regionID, :RegionVersion, :ProcMem, :Prims, :SimFPS, :PhyFPS, :OfflineTimer)'); 
            $statementAccounts->execute(['regionID' => $row['uuid'], 'RegionVersion' => $regionData->Version, 'ProcMem' => $regionData->ProcMem, 'Prims' => $regionData->Prims, 'SimFPS' => $regionData->SimFPS, 'PhyFPS' => $regionData->PhyFPS, 'OfflineTimer' => time()]);
        }
    }
?>