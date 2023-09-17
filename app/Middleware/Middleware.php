<?php
declare(strict_types=1);

namespace Mcp\Middleware;

/**
 * Middleware implementations can be registered for a RequestHandler.
 *
 * If this is done, request processing continues only if Middleware::canAccess() returns true.
 */
interface Middleware
{
    /**
     * Returns true if the request should be processed, i.e. if the client has permissionn to perform this request.
     */
    public function canAccess(): bool;

    /**
     * Called when canAcces() returns false, e.g. to redirect unauthorized users.
     */
    public function handleUnauthorized(): void;
}
