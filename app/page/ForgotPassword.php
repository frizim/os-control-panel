<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\Middleware\PreSessionMiddleware;
use Mcp\Util\SmtpClient;
use Mcp\Util\Util;

class ForgotPassword extends \Mcp\RequestHandler
{

    const MESSAGE = 'Hallo %%NAME%%,<br/><br/>wir haben soeben eine Anfrage zur Zurücksetzung des Passworts für deinen 4Creative-Account erhalten.<br/><br/>Klicke <a href="%%RESET_LINK%%">hier</a>, um ein neues Passwort festzulegen. Dieser Link läuft in 24 Stunden ab.<br/><br/>Falls du diese Anfrage nicht gesendet hast, ignoriere sie einfach. Bei weiteren Fragen kannst du uns unter info@4creative.net oder per Discord über @ikeytan erreichen.';

    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new PreSessionMiddleware($app->config('domain')));
    }

    public function get(): void
    {
        $this->app->template('forgot.php')->parent('__presession.php')->vars([
            'title' => 'Passwort vergessen',
            'message-color' => 'red'
        ])->render();
    }

    public function post(): void
    {
        $validator = new FormValidator(array(
            'username' => array('required' => true, 'regex' => '/^[^\\/<>\s]{1,64} [^\\/<>\s]{1,64}$/'),
            'email' => array('required' => true, 'regex' => '/^\S{1,64}@\S{1,250}.\S{2,64}$/')
        ));
        $tpl = $this->app->template('forgot.php')->parent('__presession.php')->var('title', 'Passwort vergessen');
        
        if (!$validator->isValid($_POST)) {
            $tpl->vars([
                'message' => 'Bitte gebe deinen Benutzernamen (Vor- und Nachname) und die dazugehörige E-Mail-Adresse ein',
                'message-color' => 'red'
            ])->render();
        } else {
            $nameParts = explode(" ", $_POST['username']);
            $email = strtolower(trim($_POST['email']));

            $getAccount = $this->app->db()->prepare('SELECT Email,FirstName,LastName,PrincipalID FROM UserAccounts WHERE FirstName = ? AND LastName = ? AND Email = ?');
            $getAccount->execute([trim($nameParts[0]), trim($nameParts[1]), $email]);
            $validRequest = $getAccount->rowCount() == 1;
            $uuid = null;
            $name = null;
            if ($res = $getAccount->fetch()) {
                $email = $res['Email'];
                $uuid = $res['PrincipalID'];
                $name = $res['FirstName'].' '.$res['LastName'];
            }

            foreach ($this->app->config('reset-blocked-domains') as $domain) {
                if (str_ends_with($email, $domain)) {
                    $validRequest = false;
                }
            }

            $tpl->vars([
                'message' => 'Falls Name und E-Mail-Adresse bei uns registriert sind, erhältst du in Kürze eine E-Mail mit weiteren Informationen.',
                'message-color' => 'green'
            ])->render();
            fastcgi_finish_request();

            if ($validRequest) {
                $getReqTime = $this->app->db()->prepare('SELECT RequestTime FROM PasswordResetTokens WHERE PrincipalID=?');
                $getReqTime->execute([$uuid]);
                if (($res = $getReqTime->fetch()) && time() - $res['RequestTime'] < 900) {
                    return;
                }

                $token = Util::generateToken(32);
                $setToken = $this->app->db()->prepare('REPLACE INTO PasswordResetTokens(PrincipalID,Token,RequestTime) VALUES(?,?,?)');
                $setToken->execute([$uuid, $token, time()]);

                $smtp = $this->app->config('smtp');
                $tplMail = $this->app->template('mail.php')->vars([
                    'title' => 'Dein Passwort zurücksetzen',
                    'preheader' => 'So kannst du ein neues Passwort für deinen 4Creative-Account festlegen'
                ])->unsafeVar('message', str_replace('%%NAME%%', $name, str_replace('%%RESET_LINK%%', 'https://'.$this->app->config('domain').'/index.php?page=reset-password&token='.$token, $this::MESSAGE)));
                (new SmtpClient($smtp['host'], $smtp['port'], $smtp['address'], $smtp['password']))->sendHtml($smtp['address'], $smtp['name'], $email, 'Zurücksetzung des Passworts für '.$name, $tplMail);
            }
        }
    }
}
