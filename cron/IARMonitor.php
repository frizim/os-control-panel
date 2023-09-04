<?php
    include_once 'app/OpenSim.php';
    $opensim = new OpenSim();

    $statement = $RUNTIME['PDO']->prepare("CREATE TABLE IF NOT EXISTS `iarstates` (`userID` VARCHAR(36) NOT NULL COLLATE 'utf8_unicode_ci', `filesize` BIGINT(20) NOT NULL DEFAULT '0', `iarfilename` VARCHAR(64) NOT NULL COLLATE 'utf8_unicode_ci', `running` INT(1) NOT NULL DEFAULT '0', PRIMARY KEY (`userID`) USING BTREE) COLLATE='utf8_unicode_ci' ENGINE=InnoDB;");
    $statement->execute();

    $statement = $RUNTIME['PDO']->prepare("SELECT userID,iarfilename,filesize FROM iarstates WHERE running = 1 LIMIT 1");
    $statement->execute();

    if ($row = $statement->fetch()) {
        $email = $opensim->getUserMail($row['userID']);

        $fullFilePath = "/var/www/html/data/".$row['iarfilename'];

        echo "Aktive IAR für ".$opensim->getUserName($row['userID'])." gefunden. File: ".$fullFilePath."\n";

        if (file_exists($fullFilePath)) {
            $filesize = filesize($fullFilePath);

            if ($filesize != $row['filesize']) {
                $statementUpdate = $RUNTIME['PDO']->prepare('UPDATE iarstates SET filesize = :filesize WHERE userID = :userID');
                $statementUpdate->execute(['filesize' => $filesize, 'userID' => $row['userID']]);
    
                echo "Status der IAR für ".$opensim->getUserName($row['userID']).": Speichert...\n";
            } else {
                $APIURL = $RUNTIME['SIDOMAN']['URL']."api.php?CONTAINER=".$RUNTIME['SIDOMAN']['CONTAINER']."&KEY=".$RUNTIME['SIDOMAN']['PASSWORD']."&METODE=RESTART";
                $APIResult = file_get_contents($APIURL);
                echo "Status der IAR für ".$opensim->getUserName($row['userID']).": Sende Mail...\n";
                $statementUpdate = $RUNTIME['PDO']->prepare('DELETE FROM iarstates WHERE userID = :userID');
                $statementUpdate->execute(['userID' => $row['userID']]);
    
                sendInworldIM("00000000-0000-0000-0000-000000000000", $row['userID'], "Inventory", $RUNTIME['GRID']['HOMEURL'], "Deine IAR ist fertig zum Download: ".$RUNTIME['IAR']['BASEURL'].$row['iarfilename']);
            }
        } else {
            $name = explode(" ", $opensim->getUserName($row['userID']));

            $APIURL = $RUNTIME['SIDOMAN']['URL']."api.php?CONTAINER=".$RUNTIME['SIDOMAN']['CONTAINER']."&KEY=".$RUNTIME['SIDOMAN']['PASSWORD']."&METODE=COMMAND&COMMAND=".urlencode("save iar ".$name[0]." ".$name[1]." /* PASSWORD /downloads/".$row['iarfilename']);
            $APIResult = file_get_contents($APIURL);

            echo "IAR für ".$name[0]." ".$name[1]." wurde gestartet: Status: ".$APIResult."\n";
        }
    } else {
        $statement = $RUNTIME['PDO']->prepare("SELECT userID,iarfilename FROM iarstates WHERE running = 0 LIMIT 1");
        $statement->execute();

        while ($row = $statement->fetch()) {
            $statementUpdate = $RUNTIME['PDO']->prepare('UPDATE iarstates SET running = :running WHERE userID = :userID');
            $statementUpdate->execute(['running' => 1, 'userID' => $row['userID']]);

            $name = explode(" ", $opensim->getUserName($row['userID']));

            $APIURL = $RUNTIME['SIDOMAN']['URL']."api.php?CONTAINER=".$RUNTIME['SIDOMAN']['CONTAINER']."&KEY=".$RUNTIME['SIDOMAN']['PASSWORD']."&METODE=COMMAND&COMMAND=".urlencode("save iar ".$name[0]." ".$name[1]." /* PASSWORD /downloads/".$row['iarfilename']);
            $APIResult = file_get_contents($APIURL);

            echo "IAR für ".$name[0]." ".$name[1]." wurde gestartet: Status: ".$APIResult."\n";
        }
    }
