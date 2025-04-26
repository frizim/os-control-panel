<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\OpenSim;
use Mcp\Middleware\LoginRequiredMiddleware;

class Dashboard extends \Mcp\RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new LoginRequiredMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {
        $opensim = new OpenSim($this->app->db());
        $this->app->template('dashboard-home.php')->parent('__dashboard.php')->vars([
            'title' => 'dashboard.title',
            'username' => $_SESSION['DISPLAYNAME'],
            'global-user-count' => $opensim->getUserCount(),
            'global-region-count' => $opensim->getRegionCount()
        ])->render();
    }
}
