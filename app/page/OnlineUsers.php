<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\OpenSim;
use Mcp\Middleware\LoginRequiredMiddleware;

class OnlineUsers extends \Mcp\RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new LoginRequiredMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {
        $opensim = new OpenSim($this->app->db());

        $table = '<table class="table"><thead><tr><th scope="col">Benutzername</th><th scope="col">Region</th></thead><tbody>';
        
        $statement = $this->app->db()->prepare("SELECT RegionID,UserID FROM Presence ORDER BY RegionID ASC");
        $statement->execute();
    
        while ($row = $statement->fetch()) {
            if ($row['RegionID'] != "00000000-0000-0000-0000-000000000000") {
                $table = $table.'<tr><td>'.htmlspecialchars(trim($opensim->getUserName($row['UserID']))).'</td><td>'.htmlspecialchars($opensim->getRegionName($row['RegionID'])).'</td></tr>';
            }
        }
    
        $this->app->template('__dashboard.php')->vars([
            'title' => 'Online Anzeige',
            'username' => $_SESSION['DISPLAYNAME']
        ])->unsafeVar('child-content', $table.'</tbody></table>')->render();
    }
}
