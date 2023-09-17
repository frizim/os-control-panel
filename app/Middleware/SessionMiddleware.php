<?php
declare(strict_types=1);

namespace Mcp\Middleware;

use UnexpectedValueException;

abstract class SessionMiddleware implements Middleware
{

    private string $cookieDomain;
    private int $cookieLifetime;

    public function __construct(string $cookieDomain, int $cookieLifetime)
    {
        $this->cookieDomain = $cookieDomain;
        $this->cookieLifetime = $cookieLifetime;
    }

    protected function handleSession(): void
    {
        switch(session_status()) {
            case PHP_SESSION_DISABLED:
                throw new UnexpectedValueException("Session functionality is disabled");
                break;
            case PHP_SESSION_NONE:
                session_set_cookie_params([
                    'lifetime' => $this->cookieLifetime,
                    'path' => '/',
                    'domain' => $this->cookieDomain,
                    'httponly' => true,
                    'secure' => true,
                    'samesite' => 'Strict'
                ]);
                session_start();
                break;
            default:
                break;
        }

        if(!isset($_SESSION['csrf']) || !preg_match('/^[0-9a-f]{64}$/', $_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
    }
}