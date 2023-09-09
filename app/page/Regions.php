<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\OpenSim;
use Mcp\Util\Util;
use Mcp\Middleware\LoginRequiredMiddleware;
use Mcp\Middleware\AdminMiddleware;

class Regions extends \Mcp\RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, isset($_GET['SHOWALL']) ? new AdminMiddleware($app, $app->config('domain')) : new LoginRequiredMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {
        $table = '<table class="table"><thead><tr><th scope="col">Region Name</th><th scope="col">Eigentümer</th><th scope="col">Position</th><th scope="col">Aktionen</th></thead><tbody>';

        $showAll = isset($_GET['SHOWALL']) && $_GET['SHOWALL'] == "1";
        $statement = $this->app->db()->prepare("SELECT uuid,regionName,owner_uuid,locX,locY FROM regions ".($showAll ? "ORDER BY owner_uuid ASC" : "WHERE owner_uuid = ? ORDER BY uuid ASC"));
        $statement->execute($showAll ? array() : array($_SESSION['UUID']));
    
        $opensim = new OpenSim($this->app->db());

        $csrf = $this->app->csrfField();
        while ($row = $statement->fetch()) {
            $stats = $this->getRegionStatsData($row['uuid']);
            $table = $table.'<tr><td>'.htmlspecialchars($row['regionName']).'<div class="blockquote-footer">'.(!empty($stats) ? 'Prims: '.$stats['Prims'].'; RAM-Nutzung: '.$stats['ProcMem'].'; SIM/PHYS FPS: '.$stats['SimFPS'].'/'.$stats['PhyFPS'].' ('.$stats['RegionVersion'].')' : 'Keine Statistik verfügbar').'</div></td><td>'.htmlspecialchars($opensim->getUserName($row['owner_uuid'])).'</td><td>'.Util::fillString(($row['locX'] / 256), 4).' / '.Util::fillString(($row['locY'] / 256), 4).'</td><td><form action="index.php?page=regions" method="post">'.$csrf.'<input type="hidden" name="region" value="'.$row['uuid'].'"><button type="submit" name="remove" class="btn btn-link btn-sm">LÖSCHEN</button></form></td></tr>';
        }

        $this->app->template('__dashboard.php')->vars([
            'title' => isset($_GET["SHOWALL"]) ? 'Regionen verwalten' : 'Deine Regionen',
            'username' => $_SESSION['DISPLAYNAME']
        ])->unsafeVar('child-content', $table.'</tbody></table>')->render();
    }

    public function post(): void
    {
        $validator = new FormValidator(array(
            'remove' => array('required' => true),
            'region' => array('required' => true, 'regex' => '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/')
        ));

        if ($validator->isValid($_POST)) {
            if (isset($_GET['SHOWALL'])) {
                $statementMembership = $this->app->db()->prepare("DELETE FROM regions WHERE uuid = ?");
                $statementMembership->execute(array($_POST['region']));
            } else {
                $statementMembership = $this->app->db()->prepare("DELETE FROM regions WHERE uuid = ? AND owner_uuid = ?");
                $statementMembership->execute(array($_POST['region'], $_SESSION['UUID']));
            }
        }

        header('Location: index.php?page=regions');
    }

    private function cleanSize($bytes)
    {
        if ($bytes > 0) {
            $unit = intval(log($bytes, 1024));
            $units = array('B', 'KB', 'MB', 'GB');
    
            if (array_key_exists($unit, $units) === true) {
                return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }
    
        return $bytes;
    }

    private function getRegionStatsData($regionID)
    {
        $statement = $this->app->db()->prepare("SELECT Prims,SimFPS,PhyFPS,ProcMem,RegionVersion FROM mcp_regions_info WHERE regionID = ?");
        $statement->execute([$regionID]);

        if ($row = $statement->fetch()) {
            $return = array();
            $return['Prims'] = strval($row['Prims']);
            $return['SimFPS'] = strval($row['SimFPS']);
            $return['PhyFPS'] = strval($row['PhyFPS']);
            $return['ProcMem'] = $this->cleanSize($row['ProcMem']);
            $return['RegionVersion'] = trim($row['RegionVersion']);

            return $return;
        }

        return array();
    }
}
