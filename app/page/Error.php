<?php
declare(strict_types=1);

namespace Mcp\Page;

class Error extends \Mcp\RequestHandler
{
    public function get(): void
    {
        http_response_code(404);
        $this->app->template('error.php')->parent('__presession.php')->vars([
            'title' => 'Seite nicht gefunden',
            'error-message' => 'Die gewünschte Seite wurde nicht gefunden.'
        ])->render();
    }
}
