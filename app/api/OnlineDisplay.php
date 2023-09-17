<?php
declare(strict_types=1);

namespace Mcp\Api;

use \Mcp\OpenSim;
use Mcp\Util\TemplateVarArray;

class OnlineDisplay extends \Mcp\RequestHandler
{

    public function get(): void
    {
        $statement = $this->app->db()->prepare("SELECT UserID,RegionID FROM Presence WHERE RegionID != '00000000-0000-0000-0000-000000000000' ORDER BY RegionID ASC");
        $statement->execute();

        $tpl = $this->app->template('online-display.php')->parent('__skeleton.php');
        $res = new TemplateVarArray();

        $opensim = new OpenSim($this->app->db());
        while ($row = $statement->fetch()) {
            $entry = new TemplateVarArray();
            $entry['name'] = trim($opensim->getUserName($row['UserID']));
            $entry['region'] = $opensim->getRegionName($row['RegionID']);
            $res[] = $entry;
        }

        $tpl->var('online-users', $res)->render();
    }
}
