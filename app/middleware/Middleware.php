<?php
declare(strict_types=1);

namespace Mcp\Middleware;

interface Middleware
{
    public function canAccess(): bool;
    public function handleUnauthorized(): void;
}