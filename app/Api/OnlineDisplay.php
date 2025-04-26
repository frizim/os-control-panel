<?php
declare(strict_types=1);

namespace Mcp\Api;

use Mcp\Util\TemplateVarArray;
use Mcp\Mcp;
use Mcp\Middleware\Middleware;

class OnlineDisplay extends \Mcp\RequestHandler
{

    private string $layout;

    public function __construct(Mcp $app, ?Middleware $mw = null, string $layout = '__skeleton.php') {
        parent::__construct($app, $mw);
        $this->layout = $layout;
    }

    public function get(): void
    {
        $statement = $this->app->db()->prepare('SELECT FirstName,LastName,regionName FROM Presence JOIN UserAccounts ON Presence.UserID = UserAccounts.PrincipalID JOIN regions ON Presence.RegionID = regions.uuid WHERE RegionID != \'00000000-0000-0000-0000-000000000000\' ORDER BY regionName ASC');
        $statement->execute();

        $tpl = $this->app->template('online-display.php')->parent($this->layout);
        $res = new TemplateVarArray();

        while ($row = $statement->fetch()) {
            $entry = new TemplateVarArray();
            $entry['name'] = trim($row['FirstName'].' '.$row['LastName']);
            $entry['region'] = $row['regionName'];
            $res[] = $entry;
        }

        $tpl->vars([
            'online-users' => $res,
            'title' => 'dashboard.user-online-state.title'
        ])->render();
    }
}
