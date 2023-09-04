<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;

    include_once 'lib/phpmailer/Exception.php';
    include_once 'lib/phpmailer/PHPMailer.php';
    include_once 'lib/phpmailer/SMTP.php';

    $statement = $RUNTIME['PDO']->prepare("CREATE TABLE IF NOT EXISTS im_offline_send (`id` int(6) NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    $statement->execute();

    function isMailAlreadySent($id)
    {
        global $RUNTIME;

        $statement = $RUNTIME['PDO']->prepare("SELECT 1 FROM im_offline_send WHERE id = ? LIMIT 1");
        $statement->execute(array($id));

        if ($statement->rowCount() != 0) {
            return true;
        }

        return false;
    }

    $IMTYP = array(
        "0"     => "eine Nachricht",
        "3"     => "eine Gruppeneinladung",
        "4"     => "ein Inventaritem",
        "5"     => "eine Bestätigung zur Annahme von Inventar",
        "6"     => "eine Information zur Ablehnung von Inventar",
        "7"     => "eine Aufforderung zur Gruppenwahl",
        "9"     => "ein Inventaritem von einem Script",
        "19"    => "eine Nachricht von einem Script",
        "32"    => "eine Gruppennachricht",
        "38"    => "eine Freundschaftsanfrage",
        "39"    => "eine Bestätigung über die Annahme der Freundschaft",
        "40"    => "eine Information über das Ablehnen der Freundschaft"
    );

    //$statement = $RUNTIME['PDO']->prepare("SELECT * FROM im_offline WHERE PrincipalID = '1148b04d-7a93-49e9-b3c9-ea0cdeec38f7'");
    $statement = $RUNTIME['PDO']->prepare("SELECT ID,PrincipalID,Message FROM im_offline");
    $statement->execute();

    while ($row = $statement->fetch()) {
        include_once 'app/OpenSim.php';
        $opensim = new OpenSim();

        $email = $opensim->getUserMail($row['PrincipalID']);
        $allowOfflineIM = $opensim->allowOfflineIM($row['PrincipalID']);

        if ($email != "" && $allowOfflineIM == "TRUE") {
            if (!isMailAlreadySent($row['ID'])) {
                $statementSend = $RUNTIME['PDO']->prepare('INSERT INTO im_offline_send (id) VALUES (:idnummer)');
                $statementSend->execute(['idnummer' => $row['ID']]);

                $mail = new PHPMailer(true);

                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $mail->isSMTP();
                $mail->Host = $RUNTIME['SMTP']['SERVER'];
                $mail->Port = $RUNTIME['SMTP']['PORT'];
                $mail->SMTPAuth = false;
    
                $mail->setFrom($RUNTIME['SMTP']['ADRESS'], $RUNTIME['GRID']['NAME']);
                $mail->addAddress($email, $opensim->getUserName($row['PrincipalID']));
    
                $XMLMESSAGE = new SimpleXMLElement($row['Message']);
    
                $HTMLMESSAGE = "Du hast ".$IMTYP["".$XMLMESSAGE->dialog.""]." in ".$RUNTIME['GRID']['NAME']." bekommen. <br><p><ul><li>".htmlspecialchars($XMLMESSAGE->message)."</li></ul></p>Gesendet von: ";
                
                if (isset($XMLMESSAGE->fromAgentName)) {
                    $HTMLMESSAGE .= $XMLMESSAGE->fromAgentName;
                }

                if (isset($XMLMESSAGE->RegionID) && isset($XMLMESSAGE->Position)) {
                    if ($XMLMESSAGE->Position->X != 0 || $XMLMESSAGE->Position->X != 0 || $XMLMESSAGE->Position->X != 0) { //TODO
                        $HTMLMESSAGE .= " @ ".$opensim->getRegionName($XMLMESSAGE->RegionID)."/".$XMLMESSAGE->Position->X."/".$XMLMESSAGE->Position->Y."/".$XMLMESSAGE->Position->Z;
                    } else {
                        $HTMLMESSAGE .= " @ ".$opensim->getRegionName($XMLMESSAGE->RegionID);
                    }
                }
                
                $HTML = new HTML();
                $HTML->importHTML("mail.html");
                $HTML->setSeitenInhalt($HTMLMESSAGE);
                $HTML->build();
    
                $mail->isHTML(true);
                $mail->Subject = "Du hast ".$IMTYP["".$XMLMESSAGE->dialog.""]." in ".$RUNTIME['GRID']['NAME'].".";
                $mail->Body    = $HTML->ausgabe();
                $mail->AltBody = strip_tags($HTMLMESSAGE);
    
                //print_r($mail);
                $mail->send();
            }else{
                //echo $row['ID']." wurde bereits gesendet.";
            }
        }else{
            //echo $row['PrincipalID']." möchte keine offline IM oder hat keine E-MAIL Adresse hinterlegt.";
        }
    }
