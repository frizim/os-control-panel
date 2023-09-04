<?php
declare(strict_types=1);

namespace Mcp\Middleware;

class AdminMiddleware extends LoginRequiredMiddleware
{
    public function canAccess(): bool
    {
        if (parent::canAccess()) {
            return $_SESSION['LEVEL'] > 100;
        }

        return false;
    }
}