<?php
declare(strict_types=1);

namespace Mcp;

use Exception;
use Mcp\Middleware\Middleware;

abstract class RequestHandler
{

    protected Mcp $app;
    private ?Middleware $middleware;

    public function __construct(Mcp $app, Middleware $mw = null)
    {
        $this->app = $app;
        $this->middleware = $mw;
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'POST') {
            http_response_code(400);
            exit();
        }

        if ($this->middleware != null) {
            try {
                if (!$this->middleware->canAccess()) {
                    $this->middleware->handleUnauthorized();
                    exit();
                }
            } catch (Exception $e) {
                error_log("Middleware handling raised an exception: " + $e->getMessage());
                http_response_code(500);
                exit();
            }
        }

        $_SERVER['REQUEST_METHOD'] == 'GET' ? $this->get() : $this->post();
    }

    public function get(): void
    {
        http_response_code(405);
    }

    public function post(): void
    {
        http_response_code(405);
    }

}
