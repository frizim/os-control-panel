<?php
declare(strict_types=1);

namespace Mcp\Api;

use \Mcp\OpenSim;

class OnlineDisplay extends \Mcp\RequestHandler
{

    public function get(): void
    {
        $statement = $this->app->db()->prepare("SELECT UserID,RegionID FROM Presence WHERE RegionID != '00000000-0000-0000-0000-000000000000' ORDER BY RegionID ASC");
        $statement->execute();

        $tpl = $this->app->template('online-display.php');
        if ($statement->rowCount() == 0) {
            $tpl->unsafeVar('online-users', '<h1 style="text-align: center; margin-top: 60px">Es ist niemand online!</h1>');
        } else {
            $table = '<table style="width:350px;margin-left:auto;margin-right:auto;margin-top:25px"><tr><th align="left" style="background-color: #FF8000;">Name</th><th align="left" style="background-color: #FF8000;">Region</th></tr>';
            $entryColor = true;
            $opensim = new OpenSim($this->app->db());
            while ($row = $statement->fetch()) {
                $table = $table.'<tr style="background-color: '.($entryColor ? '#F2F2F2' : '#E6E6E6').';"><td>'.trim($opensim->getUserName($row['UserID'])).'</td><td>'.$opensim->getRegionName($row['RegionID']).'</td></tr>';
                $entryColor = !$entryColor;
            }

            $tpl->unsafeVar('online-users', $table.'</table>');
        }

        $tpl->render();
    }
}
