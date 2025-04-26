<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\Middleware\PreSessionMiddleware;
use Mcp\Util\SmtpClient;
use Mcp\Util\TemplateVarArray;
use Mcp\Util\Util;

class ForgotPassword extends \Mcp\RequestHandler
{

    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new PreSessionMiddleware($app->config('domain')));
    }

    public function get(): void
    {
        $this->app->template('forgot.php')->parent('__presession.php')->vars([
            'title' => 'forgotPassword.title',
            'message-color' => 'red'
        ])->render();
    }

    public function post(): void
    {
        $validator = new FormValidator(array(
            'username' => array('required' => true, 'regex' => '/^[^\\/<>\s]{1,64} [^\\/<>\s]{1,64}$/'),
            'email' => array('required' => true, 'regex' => '/^\S{1,64}@\S{1,250}.\S{2,64}$/')
        ));
        $tpl = $this->app->template('forgot.php')->parent('__presession.php')->var('title', 'forgotPassword.title');
        
        if (!$validator->isValid($_POST)) {
            $tpl->vars([
                'message' => 'forgotPassword.invalid',
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
                'message' => 'forgotPassword.success',
                'message-color' => 'green'
            ])->render();

            if(function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            if ($validRequest) {
                $getReqTime = $this->app->db()->prepare('SELECT RequestTime FROM mcp_password_reset WHERE PrincipalID=?');
                $getReqTime->execute([$uuid]);
                if (($res = $getReqTime->fetch()) && time() - $res['RequestTime'] < 900) {
                    return;
                }

                $token = Util::generateToken(32);
                $setToken = $this->app->db()->prepare('REPLACE INTO mcp_password_reset(PrincipalID,Token,RequestTime) VALUES(?,?,?)');
                $setToken->execute([$uuid, $token, time()]);

                $smtp = $this->app->config('smtp');

                $tplMail = $this->app->template('password-reset.php')->parent('mail.php');
                $subject = $tplMail->getI18n()->t('email.passwordReset.subject', new TemplateVarArray(['name' => $name]));
                $tplMail->vars([
                    'title' => $subject,
                    'preheader' => 'email.passwordReset.preheader',
                    'name' => $name,
                    'reset-link' => "https://".$this->app->config("domain").'/index.php?page=reset-password&token='.$token
                ]);
                (new SmtpClient($smtp['host'], intval($smtp['port']), $smtp['address'], $smtp['password']))->sendHtml($smtp['address'], $smtp['name'], $email, $subject, $tplMail);
            }
        }
    }
}
