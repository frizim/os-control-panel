<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\Middleware\PreSessionMiddleware;
use Mcp\Util\SmtpClient;
use Mcp\Util\Util;

class ResetPassword extends \Mcp\RequestHandler
{

    private const MESSAGE = 'Hallo %%NAME%%,<br/><br/>das Passwort für deinen 4Creative-Account wurde soeben über die Funktion "Passwort vergessen" geändert.<br/><br/>Solltest du diese Änderung nicht selbst durchgeführt haben, wende dich bitte umgehend per E-Mail (info@4creative.net) oder Discord (@ikeytan) an uns.';
    private const TOKEN_INVALID = 'Dieser Link zur Passwortzurücksetzung ist nicht gültig. Bitte klicke oder kopiere den Link aus der E-Mail, die du erhalten hast.';
    private const TOKEN_EXPIRED = 'Dein Link zur Passwortzurücksetzung ist abgelaufen. Klicke <a href="index.php?page=forgot">hier</a>, um eine neue Anfrage zu senden.';

    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new PreSessionMiddleware($app->config('domain')));
    }

    public function get(): void
    {
        $this->displayPage();
    }

    public function post(): void
    {
        $validator = new FormValidator(array(
            'password' => array('required' => true, 'regex' => '/^.{1,1000}$/'),
            'passwordRepeat' => array('required' => true, 'regex' => '/^.{1,1000}$/'),
            'resetToken' => array('required' => true, 'regex' => '/^[a-zA-Z0-9]{32}$/')
        ));

        if ($validator->isValid($_POST)) {
            if ($_POST['password'] !== $_POST['passwordRepeat']) {
                $this->displayPage('Du musst in beiden Feldern das gleiche Passwort eingeben');
                return;
            }

            if (strlen($_POST['password']) < $this->app->config('password-min-length')) {
                $this->displayPage('Dein Passwort muss mindestens '.$this->app->config('password-min-length').' Zeichen lang sein.');
                return;
            }

            $getReq = $this->app->db()->prepare('SELECT UserAccounts.PrincipalID AS UUID,FirstName,LastName,Email,Token,RequestTime FROM mcp_password_reset JOIN UserAccounts ON UserAccounts.PrincipalID = mcp_password_reset.PrincipalID WHERE Token = ?');
            $getReq->execute([$_POST['resetToken']]);
            $res = $getReq->fetch();

            if (!$res || !hash_equals($res['Token'], $_POST['resetToken'])) {
                $this->displayTokenError($this::TOKEN_INVALID);
                return;
            }

            $uuid = $res['UUID'];
            $name = $res['FirstName'].' '.$res['LastName'];
            $getToken = $this->app->db()->prepare('DELETE FROM mcp_password_reset WHERE PrincipalID = ? AND Token = ?');
            $getToken->execute([$uuid, $_POST['resetToken']]);
            if ($getToken->rowCount() == 0) {
                $this->displayTokenError($this::TOKEN_INVALID);
                return;
            }

            if (time() - $res['RequestTime'] > 86400) {
                $this->displayTokenError($this::TOKEN_EXPIRED);
                return;
            }

            $salt = bin2hex(random_bytes(16));
            $hash = md5(md5(trim($_POST['password'])).':'.$salt);
            $statement = $this->app->db()->prepare('UPDATE auth SET passwordHash = :PasswordHash, passwordSalt = :PasswordSalt WHERE UUID = :PrincipalID');
            $statement->execute(['PasswordHash' => $hash, 'PasswordSalt' => $salt, 'PrincipalID' => $uuid]);

            session_unset();
            $_SESSION['loginMessage'] = 'Du kannst dich jetzt mit deinem neuen Passwort einloggen!';
            $_SESSION['loginMessageColor'] = 'darkgreen';

            $smtp = $this->app->config('smtp');
            $tplMail = $this->app->template('mail.php')->vars([
                'title' => 'Passwort geändert',
                'preheader' => 'Das Passwort für deinen 4Creative-Account wurde soeben zurückgesetzt'
            ])->unsafeVar('message', str_replace('%%NAME%%', $name, $this::MESSAGE));
            (new SmtpClient($smtp['host'], $smtp['port'], $smtp['address'], $smtp['password']))->sendHtml($smtp['address'], $smtp['name'], $res['Email'], 'Passwort für '.$name.' zurückgesetzt', $tplMail);

            header('Location: index.php?page=login');
        }
    }

    private function displayTokenError(string $message): void
    {
        $this->app->template('error.php')->parent('__presession.php')->vars([
            'title' => 'Fehler',
            'message' => $message
        ])->render();
    }

    private function displayPage(string $message = ''): void
    {
        if (!isset($_GET['token']) || !preg_match('/^[a-z0-9A-Z]{32}$/', $_GET['token'])) {
            $this->displayTokenError($this::TOKEN_INVALID);
            return;
        }

        $this->app->template('reset-password.php')->parent('__presession.php')->vars([
            'title' => 'Neues Passwort festlegen',
            'message' => $message,
            'reset-token' => $_GET['token']
        ])->render();
    }
}
