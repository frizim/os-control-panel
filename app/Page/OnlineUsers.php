<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\OpenSim;
use Mcp\Middleware\LoginRequiredMiddleware;
use Mcp\Util\TemplateVarArray;

class OnlineUsers extends \Mcp\RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new LoginRequiredMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {
        $opensim = new OpenSim($this->app->db());

        $statement = $this->app->db()->prepare("SELECT RegionID,UserID FROM Presence ORDER BY RegionID ASC");
        $statement->execute();
    
        $users = new TemplateVarArray();
        while($row = $statement->fetch()) {
            if ($row['RegionID'] != "00000000-0000-0000-0000-000000000000") {
                $user = new TemplateVarArray();
                $user["name"] = trim($opensim->getUserName($row['UserID']));
                $user["region"] = $opensim->getRegionName($row['RegionID']);
                $users[] = $user;
            }
        }
    
        $this->app->template('online-display.php')->parent('__dashboard.php')->vars([
            'title' => 'Online Anzeige',
            'username' => $_SESSION['DISPLAYNAME'],
            'online-users' => $users
        ])->render();
    }
}
