<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\Middleware\LoginRequiredMiddleware;

class OnlineUsers extends \Mcp\Api\OnlineDisplay
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new LoginRequiredMiddleware($app, $app->config('domain')), '__dashboard.php');
    }

}
