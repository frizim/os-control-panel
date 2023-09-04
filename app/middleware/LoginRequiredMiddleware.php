<?php
declare(strict_types=1);

namespace Mcp\Middleware;

use Mcp\ConnectionProvider;

class LoginRequiredMiddleware extends SessionMiddleware
{

    private ConnectionProvider $connProvider;

    public function __construct(ConnectionProvider $connProvider, string $cookieDomain)
    {
        parent::__construct($cookieDomain, 3600);
        $this->connProvider = $connProvider;
    }

    public function canAccess(): bool
    {
        parent::handleSession();
        if (isset($_SESSION['UUID'])) {
            // User level or existence of account may have changed since session was created
            $getLevel = $this->connProvider->db()->prepare('SELECT UserLevel FROM UserAccounts WHERE PrincipalID = ?');
            $getLevel->execute([$_SESSION['UUID']]);
            if ($row = $getLevel->fetch()) {
                $_SESSION['LEVEL'] = $row['UserLevel'];
                return true;
            }
            else {
                session_unset();
                session_destroy();
                return false;
            }
        }

        return false;
    }

    public function handleUnauthorized(): void
    {
        header('Location: index.php?page=login');
    }
}