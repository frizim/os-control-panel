<?php
declare(strict_types=1);

namespace Mcp;

use PDO;

interface ConnectionProvider
{
    public function db(): PDO;
}