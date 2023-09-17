<?php
declare(strict_types=1);

namespace Mcp\Middleware;

class PreSessionMiddleware extends SessionMiddleware
{
    public function __construct(string $cookieDomain)
    {
        parent::__construct($cookieDomain, 0);
    }

    public function canAccess(): bool
    {
        parent::handleSession();
        return !isset($_SESSION['LOGIN']);
    }

    public function handleUnauthorized(): void
    {
        header('Location: index.php');
    }
}