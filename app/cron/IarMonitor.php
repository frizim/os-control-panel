<?php
declare(strict_types=1);

namespace Mcp\Cron;

use Mcp\OpenSim;
use Mcp\Opensim\RestConsole;
use Mcp\Util\Util;

class IarMonitor extends CronJob
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, Frequency::EACH_MINUTE);
    }

    public function run(): bool
    {
        $opensim = new OpenSim($this->app->db());
    
        $statement = $this->app->db()->prepare("SELECT userID,iarfilename,filesize,state,created FROM mcp_iar_state WHERE state < ?");
        $statement->execute([2]);
    
        while ($row = $statement->fetch()) {
            if ($row['state'] == 0) { // 0 - Request to OS pending
                $name = explode(' ', $opensim->getUserName($row['userID']));
                
                $restCfg = $this->app->config('restconsole');
                $restConsole = new RestConsole($restCfg['host'], intval($restCfg['port']));
                if ($restConsole->startSession($restCfg['user'], $restCfg['password']) && $restConsole->sendCommand('save iar '.$name[0].' '.$name[1].' /* password '.$restCfg['os-iar-path'].$row['iarfilename'])) {
                    $statementUpdate = $this->app->db()->prepare('UPDATE mcp_iar_state SET state = ? WHERE userID = ?');
                    $statementUpdate->execute([1, $row['userID']]);
                }
            } elseif ($row['state'] == 1) { // 1 - IAR Creation in progress
                $fullFilePath = $this->app->getDataDir().DIRECTORY_SEPARATOR.'iars'.DIRECTORY_SEPARATOR.$row['iarfilename'];
                if (file_exists($fullFilePath)) {
                    $filesize = filesize($fullFilePath);
        
                    if ($filesize != $row['filesize']) {
                        $statementUpdate = $this->app->db()->prepare('UPDATE mcp_iar_state SET filesize = ? WHERE userID = ?');
                        $statementUpdate->execute([$filesize, $row['userID']]);
                    } else {
                        $statementUpdate = $this->app->db()->prepare('UPDATE mcp_iar_state SET state = ?, created = ? WHERE userID = ?');
                        $statementUpdate->execute([2, time(), $row['userID']]);
                        
                        Util::sendInworldIM("00000000-0000-0000-0000-000000000000", $row['userID'], "Inventory", $this->app->config('grid')['homeurl'], "Deine IAR ist fertig zum Download: https://".$this->app->config('domain').'/iars/'.$row['iarfilename']);
                    }
                }
            }
        }

        // 2 - IAR creation finished; delete if expired
        $weekOld = time() - 604800;
        $statementExpired = $this->app->db()->prepare('SELECT userID,iarfilename FROM mcp_iar_state WHERE state = ? AND created < ?');
        $statementExpired->execute([2, $weekOld]);
        $statementDeleteExpired = $this->app->db()->prepare('DELETE FROM mcp_iar_state WHERE state = ? AND userID = ?');
        while ($row = $statementExpired->fetch()) {
            $fullFilePath = $this->app->getDataDir().DIRECTORY_SEPARATOR.'iars'.DIRECTORY_SEPARATOR.$row['iarfilename'];
            if (file_exists($fullFilePath) && unlink($fullFilePath)) {
                $statementDeleteExpired->execute([2, $row['userID']]);
            }
        }

        return true;
    }
}
