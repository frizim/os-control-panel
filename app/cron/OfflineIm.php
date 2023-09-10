<?php
declare(strict_types=1);

namespace Mcp\Cron;

use Mcp\OpenSim;
use Mcp\Util\SmtpClient;

use SimpleXMLElement;

class OfflineIm extends CronJob
{
    private const IM_TYPE = array(
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

    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, Frequency::EACH_MINUTE);
    }

    public function run(): bool
    {
        $statement = $this->app->db()->prepare("SELECT ID,PrincipalID,Message FROM im_offline");
        $statement->execute();

        while ($row = $statement->fetch()) {
            $opensim = new OpenSim($this->app->db());

            $email = $opensim->getUserMail($row['PrincipalID']);
            $allowOfflineIM = $opensim->allowOfflineIM($row['PrincipalID']);

            if ($email != "" && $allowOfflineIM == "TRUE" && !$this->isMailAlreadySent($row['ID'])) {
                $statementSend = $this->app->db()->prepare('INSERT INTO mcp_offlineim_send (id) VALUES (:idnummer)');
                $statementSend->execute(['idnummer' => $row['ID']]);

                $mailcfg = $this->app->config('smtp');
                $smtpClient = new SmtpClient($mailcfg['host'], intval($mailcfg['port']), $mailcfg['address'], $mailcfg['password']);

                $xmlMessage = new SimpleXMLElement($row['Message']);

                $imType = $this::IM_TYPE["" . $xmlMessage->dialog . ""];
                $htmlMessage = "Du hast " . $imType . " in " . $this->app->config('grid')['name'] . " bekommen. <br><p><ul><li>" . htmlspecialchars($xmlMessage->message) . "</li></ul></p>Gesendet von: ";

                if (isset($xmlMessage->fromAgentName)) {
                    $htmlMessage .= $xmlMessage->fromAgentName;
                }

                if (isset($xmlMessage->RegionID) && isset($xmlMessage->Position)) {
                    if ($xmlMessage->Position->X != 0 || $xmlMessage->Position->Y != 0 || $xmlMessage->Position->Z != 0) { //TODO
                        $htmlMessage .= " @ " . $opensim->getRegionName($xmlMessage->RegionID) . "/" . $xmlMessage->Position->X . "/" . $xmlMessage->Position->Y . "/" . $xmlMessage->Position->Z;
                    } else {
                        $htmlMessage .= " @ " . $opensim->getRegionName($xmlMessage->RegionID);
                    }
                }

                $tpl = $this->app->template('mail.php')->vars([
                    'title' => substr($imType, strpos($imType, ' '))
                ])->unsafeVar('message', $htmlMessage);
                $smtpClient->sendHtml($mailcfg['address'], $mailcfg['name'], $email, "Du hast " . $imType . " in " . $this->app->config('grid')['name'] . ".", $tpl);
            }
        }
        return true;
    }

    private function isMailAlreadySent($id): bool
    {
        $statement = $this->app->db()->prepare("SELECT 1 FROM mcp_offlineim_send WHERE id = ? LIMIT 1");
        $statement->execute(array($id));

        if ($statement->rowCount() != 0) {
            return true;
        }

        return false;
    }

}
