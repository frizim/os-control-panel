<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\Util\Util;

class Error extends \Mcp\RequestHandler
{
    public function get(): void
    {
        http_response_code(404);
        Util::displayError($this->app, 'Die gewünschte Seite wurde nicht gefunden.');
    }
}
