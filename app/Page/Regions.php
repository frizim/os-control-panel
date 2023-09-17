<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\OpenSim;
use Mcp\Util\Util;
use Mcp\Middleware\LoginRequiredMiddleware;
use Mcp\Middleware\AdminMiddleware;
use Mcp\Util\TemplateVarArray;

class Regions extends \Mcp\RequestHandler
{
    private bool $showAll;

    public function __construct(\Mcp\Mcp $app)
    {
        $this->showAll = isset($_GET['SHOWALL']) && $_GET['SHOWALL'] == "1";
        parent::__construct($app, $this->showAll ? new AdminMiddleware($app, $app->config('domain')) : new LoginRequiredMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {


        $statement = $this->app->db()->prepare("SELECT uuid,regionName,owner_uuid,locX,locY FROM regions ".($this->showAll ? "ORDER BY owner_uuid ASC" : "WHERE owner_uuid = ? ORDER BY uuid ASC"));
        $statement->execute($this->showAll ? array() : array($_SESSION['UUID']));
    
        $opensim = new OpenSim($this->app->db());

        $regions = new TemplateVarArray();
        while ($row = $statement->fetch()) {
            $region = new TemplateVarArray();
            $stats = $this->getRegionStatsData($row['uuid']);
            $region['uuid'] = $row['uuid'];
            $region['stats'] = $stats;
            $region['name'] = $row['regionName'];
            $region['owner_name'] = $opensim->getUserName($row['owner_uuid']);
            $region['locX'] = Util::fillString(($row['locX'] / 256), 4);
            $region['locY'] = Util::fillString(($row['locY'] / 256), 4);

            $regions[] = $region;
        }

        $this->app->template('__dashboard.php')->vars([
            'title' => $this->showAll ? 'Regionen verwalten' : 'Deine Regionen',
            'username' => $_SESSION['DISPLAYNAME'],
            'showall' => $this->showAll ? '&SHOWALL=1' : '',
            'regions' => $regions
        ])->render();
    }

    public function post(): void
    {
        $validator = new FormValidator(array(
            'region' => array('required' => true, 'regex' => '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/')
        ));

        if (isset($_POST['remove']) && $validator->isValid($_POST)) {
            if (isset($_GET['SHOWALL'])) {
                $statementMembership = $this->app->db()->prepare("DELETE FROM regions WHERE uuid = ?");
                $statementMembership->execute(array($_POST['region']));
            } else {
                $statementMembership = $this->app->db()->prepare("DELETE FROM regions WHERE uuid = ? AND owner_uuid = ?");
                $statementMembership->execute(array($_POST['region'], $_SESSION['UUID']));
            }
        }

        header('Location: index.php?page=regions'.($this->showAll ? '&SHOWALL=1' : ''));
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
