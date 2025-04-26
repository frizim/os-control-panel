<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\Middleware\PreSessionMiddleware;
use Mcp\Util\SmtpClient;
use Mcp\Util\TemplateVarArray;

class ResetPassword extends \Mcp\RequestHandler
{

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
                $this->displayPage('resetPassword.error.passwordsNotMatching');
                return;
            }

            if (strlen($_POST['password']) < $this->app->config('password-min-length')) {
                $this->displayPage('register.error.passwordTooShort', [$this->app->config('password-min-length')]);
                return;
            }

            $getReq = $this->app->db()->prepare('SELECT UserAccounts.PrincipalID AS UUID,FirstName,LastName,Email,Token,RequestTime FROM mcp_password_reset JOIN UserAccounts ON UserAccounts.PrincipalID = mcp_password_reset.PrincipalID WHERE Token = ?');
            $getReq->execute([$_POST['resetToken']]);
            $res = $getReq->fetch();

            if (!$res || !hash_equals($res['Token'], $_POST['resetToken'])) {
                $this->displayTokenError('resetPassword.error.tokenInvalid');
                return;
            }

            $uuid = $res['UUID'];
            $name = $res['FirstName'].' '.$res['LastName'];
            $getToken = $this->app->db()->prepare('DELETE FROM mcp_password_reset WHERE PrincipalID = ? AND Token = ?');
            $getToken->execute([$uuid, $_POST['resetToken']]);
            if ($getToken->rowCount() == 0) {
                $this->displayTokenError('resetPassword.error.tokenInvalid');
                return;
            }

            if (time() - $res['RequestTime'] > 86400) {
                $this->displayTokenError('resetPassword.error.tokenExpired');
                return;
            }

            $salt = bin2hex(random_bytes(16));
            $hash = md5(md5(trim($_POST['password'])).':'.$salt);
            $statement = $this->app->db()->prepare('UPDATE auth SET passwordHash = :PasswordHash, passwordSalt = :PasswordSalt WHERE UUID = :PrincipalID');
            $statement->execute(['PasswordHash' => $hash, 'PasswordSalt' => $salt, 'PrincipalID' => $uuid]);

            session_unset();
            $_SESSION['loginMessage'] = 'resetPassword.success';
            $_SESSION['loginMessageColor'] = 'darkgreen';

            $smtp = $this->app->config('smtp');
            
            $tplMail = $this->app->template('password-reset-notification.php')->parent("mail.php");

            $subject = $tplMail->getI18n()->t('email.passwordResetNotification.subject', new TemplateVarArray(['name' => $name]));
            $tplMail->vars([
                'title' => $subject,
                'preheader' => 'email.passwordResetNotification.preheader',
                'name' => $name
            ]);
            (new SmtpClient($smtp['host'], intval($smtp['port']), $smtp['address'], $smtp['password']))->sendHtml($smtp['address'], $smtp['name'], $res['Email'], $subject, $tplMail);

            header('Location: index.php?page=login');
        }
    }

    private function displayTokenError(string $message): void
    {
        $this->app->template('error.php')->parent('__presession.php')->vars([
            'title' => 'error.title',
            'error-message' => $message
        ])->render();
    }

    private function displayPage(string $message = '', ?array $params = null): void
    {
        if (!isset($_GET['token']) || !preg_match('/^[a-z0-9A-Z]{32}$/', $_GET['token'])) {
            $this->displayTokenError('resetPassword.error.tokenInvalid');
            return;
        }

        $this->app->template('reset-password.php')->parent('__presession.php')->vars([
            'title' => 'resetPassword.title',
            'message' => $message,
            'message-params' => $params,
            'reset-token' => $_GET['token'],
            'pwMinLength' => $this->app->config('password-min-length')
        ])->render();
    }
}
