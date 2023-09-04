<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\Middleware\PreSessionMiddleware;

class Login extends \Mcp\RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new PreSessionMiddleware($app->config('domain')));
    }

    public function get(): void
    {
        $tpl = $this->app->template('login.php')->parent('__presession.php')->var('title', 'Login')->var('last-username', '');

        if (isset($_SESSION) && isset($_SESSION['loginMessage'])) {
            $tpl->vars([
                'message' => $_SESSION['loginMessage'],
                'message-color' => $_SESSION['loginMessageColor']
            ]);
            unset($_SESSION['loginMessage']);
            unset($_SESSION['loginMessageColor']);
        } else {
            $tpl->vars([
                'message' => '',
                'message-color' => 'red'
            ]);
        }

        $tpl->render();
    }

    public function post(): void
    {
        $validator = new FormValidator(array(
            'username' => array('required' => true, 'regex' => '/^[^\\/<>\s]{1,64} [^\\/<>\s]{1,64}$/'),
            'password' => array('required' => true, 'regex' => '/^.{1,1000}$/')
        ));
        
        $tpl = $this->app->template('login.php')->parent('__presession.php')->var('title', 'Login');
        if (!$validator->isValid($_POST)) {
            $tpl->vars([
                'message' => 'Bitte gebe Benutzername (Vor- und Nachname) und Passwort ein.',
                'message-color' => 'red',
                'last-username'=> ''
            ])->render();
        } else {
            $statementUser = $this->app->db()->prepare("SELECT PrincipalID,FirstName,LastName,Email,UserLevel,passwordHash,passwordSalt FROM UserAccounts JOIN auth ON UserAccounts.PrincipalID = auth.UUID WHERE FirstName = ? AND LastName = ? LIMIT 1");
            $statementUser->execute(explode(" ", trim($_POST['username'])));
            $res = ['passwordHash' => '', 'passwordSalt' => ''];

            if ($rowUser = $statementUser->fetch()) {
                $res = $rowUser;
            }

            if (hash_equals(md5(md5($_POST['password']).":".$res['passwordSalt']), $res['passwordHash'])) {
                session_abort();
                session_set_cookie_params([
                    'lifetime' => 86400,
                    'path' => '/',
                    'domain' => $this->app->config('domain'),
                    'httponly' => true,
                    'secure' => true,
                    'samesite' => 'Strict'
                ]);
                session_start();
                session_regenerate_id(true);
                $_SESSION['FIRSTNAME'] = $rowUser['FirstName'];
                $_SESSION['LASTNAME'] = $rowUser['LastName'];
                $_SESSION['EMAIL'] = $rowUser['Email'];
                $_SESSION['PASSWORD'] = $rowUser['passwordHash'];
                $_SESSION['SALT'] = $rowUser['passwordSalt'];
                $_SESSION['UUID'] = $rowUser['PrincipalID'];
                $_SESSION['LEVEL'] = $rowUser['UserLevel'];
                $_SESSION['DISPLAYNAME'] = strtoupper($rowUser['FirstName'].' '.$rowUser['LastName']);
                $_SESSION['LOGIN'] = 'true';

                header("Location: index.php?page=dashboard");
                die();
            }

            $tpl->vars([
                'message' => 'Benutzername und/oder Passwort falsch.',
                'message-color' => 'red',
                'last-username' => $_POST['username']
            ])->render();
        }
    }
}
