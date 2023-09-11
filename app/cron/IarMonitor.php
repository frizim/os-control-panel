<?php
declare(strict_types=1);

namespace Mcp\Cron;

use Mcp\OpenSim;
use Mcp\Opensim\RestConsole;
use Mcp\Util\Util;

class IarMonitor extends CronJob
{

    private ?RestConsole $console;
    private bool $consoleAvailable = true;

    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, Frequency::EACH_MINUTE);
    }

    public function run(): bool
    {
        $opensim = new OpenSim($this->app->db());

        $dirPath = $this->app->getDataDir().DIRECTORY_SEPARATOR.'iars';
        if (!is_dir($dirPath)) {
            mkdir($dirPath);
        }
    
        $statement = $this->app->db()->prepare("SELECT userID,iarfilename,filesize,state FROM mcp_iar_state WHERE state < ?");
        $statement->execute([2]);

        if ($statement->rowCount() > 0) {
            while ($row = $statement->fetch()) {
                if ($row['state'] == 0) { // 0 - Request to OS pending
                    if ($this->console() === false) {
                        continue;
                    }

                    $name = explode(' ', $opensim->getUserName($row['userID']));
                    if ($this->console()->sendCommand('save iar '.$name[0].' '.$name[1].' /* password '.$this->app->config('iarfetcher')['os-iar-path'].$row['iarfilename'])) {
                        $statementUpdate = $this->app->db()->prepare('UPDATE mcp_iar_state SET state = ? WHERE userID = ?');
                        $statementUpdate->execute([1, $row['userID']]);
                    }
                } elseif ($row['state'] == 1) { // 1 - IAR Creation in progress
                    $fullFilePath = $dirPath.DIRECTORY_SEPARATOR.$row['iarfilename'];
                    if (file_exists($fullFilePath)) {
                        $filesize = filesize($fullFilePath);
            
                        if ($filesize != $row['filesize']) {
                            $statementUpdate = $this->app->db()->prepare('UPDATE mcp_iar_state SET filesize = ? WHERE userID = ?');
                            $statementUpdate->execute([$filesize, $row['userID']]);
                        } else {
                            $statementUpdate = $this->app->db()->prepare('UPDATE mcp_iar_state SET state = ?, created = ? WHERE userID = ?');
                            $statementUpdate->execute([2, time(), $row['userID']]);
                            
                            Util::sendInworldIM("00000000-0000-0000-0000-000000000000", $row['userID'], "Inventory", $this->app->config('grid')['homeurl'], "Deine IAR ist fertig zum Download: https://".$this->app->config('domain').'/index.php?api=downloadIar&id='.substr($row['iarfilename'], 0, strlen($row['iarfilename']) - 4));
                        }
                    }
                }
            }

            if ($this->consoleAvailable) {
                $this->console->closeSession();
            }
        }
    
        // 2 - IAR creation finished; delete if expired
        $weekOld = time() - 604800;
        $statementExpired = $this->app->db()->prepare('SELECT userID,iarfilename FROM mcp_iar_state WHERE state = ? AND created < ?');
        $statementExpired->execute([2, $weekOld]);
        $statementDeleteExpired = $this->app->db()->prepare('DELETE FROM mcp_iar_state WHERE state = ? AND userID = ?');
        while ($row = $statementExpired->fetch()) {
            $fullFilePath = $dirPath.DIRECTORY_SEPARATOR.$row['iarfilename'];
            if (file_exists($fullFilePath) && unlink($fullFilePath)) {
                $statementDeleteExpired->execute([2, $row['userID']]);
            }
        }

        return true;
    }

    private function console(): RestConsole|bool
    {
        if (!$this->consoleAvailable) {
            return false;
        }

        if ($this->console == null) {
            $restCfg = $this->app->config('iarfetcher');
            $console = new RestConsole($restCfg['host'], intval($restCfg['port']));
            if ($console->startSession($restCfg['user'], $restCfg['password'])) {
                $this->console = $console;
            }
            else {
                $this->consoleAvailable = false;
                return false;
            }
        }

        return $this->console;
    }
}
