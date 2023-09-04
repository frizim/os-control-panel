<?php
declare(strict_types=1);

namespace Mcp\Util;

/**
 * This class can be used like a regular array, but is guaranteed to return a value.
 * Keys not set in the underlying array return an empty string.
 */
class TemplateVarArray implements \ArrayAccess
{

    private array $vars;

    public function __construct(array $vars)
    {
        $this->vars = $vars;
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return isset($this->vars[$offset]) ? $this->vars[$offset] : '';
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->vars[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->vars[$offset]);
    }
}
