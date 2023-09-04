<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\OpenSim;
use Mcp\RequestHandler;
use Mcp\Util\Util;
use Mcp\Middleware\AdminMiddleware;

class ManageUsers extends RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new AdminMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {
        $table = '<table class="table"><thead><tr><th scope="col">Vorname</th><th scope="col">Nachname</th><th scope="col">Status</th><th scope="col">Aktionen</th></thead><tbody>';
    
        // Only select current primary account
        $statement = $this->app->db()->prepare("SELECT FirstName,LastName,UserLevel,PrincipalID FROM UserAccounts JOIN auth ON auth.UUID = UserAccounts.PrincipalID ORDER BY Created ASC");
        $statement->execute();
    
        $statementIdent = $this->app->db()->prepare("SELECT FirstName,LastName,UserLevel,IdentityID FROM UserIdentitys JOIN UserAccounts ON UserAccounts.PrincipalID = UserIdentitys.IdentityID WHERE UserIdentitys.PrincipalID = ? AND UserIdentitys.PrincipalID != UserIdentitys.IdentityID");
        $csrf = $this->app->csrfField();
        while ($row = $statement->fetch()) {
            $entry = '<tr><td>'.htmlspecialchars($row['FirstName']).'</td><td>'.htmlspecialchars($row['LastName']).'</td><td>'.htmlspecialchars(strval($row['UserLevel'])).'</td><td><form action="index.php?page=users" method="post">'.$csrf.'<input type="hidden" name="userid" value="'.htmlspecialchars($row['PrincipalID']).'"><button type="submit" name="genpw" class="btn btn-link btn-sm">PASSWORT ZURÜCKSETZEN</button> <button type="submit" name="deluser" class="btn btn-link btn-sm" style="color: red">LÖSCHEN</button></form></td></tr>';
            $statementIdent->execute([$row['PrincipalID']]);
            while ($identRow = $statementIdent->fetch()) {
                $entry = $entry.'<tr class="ident-row"><td>'.htmlspecialchars($identRow['FirstName']).'</td><td>'.htmlspecialchars($identRow['LastName']).'</td><td>'.htmlspecialchars(strval($identRow['UserLevel'])).'</td><td><form action="index.php?page=users" method="post">'.$csrf.'<input type="hidden" name="userid" value="'.htmlspecialchars($row['PrincipalID']).'"><input type="hidden" name="identid" value="'.htmlspecialchars($identRow['IdentityID']).'"><button type="submit" name="delident" class="btn btn-link btn-sm">Identität löschen</button></form></td></tr>';
            }
            $table = $table.$entry;
        }
    
        $tpl = $this->app->template('users.php')->parent('__dashboard.php')->var('title', 'Benutzer')->unsafeVar('user-list', $table.'</tbody></table>')
            ->unsafeVar('users-message', isset($_SESSION['users-message']) ? $_SESSION['users-message'] : '')
            ->unsafeVar('custom-css', '<link rel="stylesheet" href="./style/admin-users.css">');

        if (isset($_SESSION['users-message'])) {
            $tpl->unsafeVar('users-message', $_SESSION['users-message']);
            unset($_SESSION['users-message']);
        }

        if (isset($_SESSION['invite-id'])) {
            $tpl->var('invite-link', 'https://'.$this->app->config('domain').'/index.php?page=register&code='.$_SESSION['invite-id']);
            unset($_SESSION['invite-id']);
        }

        $tpl->render();
    }

    public function post(): void
    {
        if (isset($_POST['generateLink'])) {
            $validator = new FormValidator(array()); // Needed only for CSRF token validation

            if ($validator->isValid($_POST)) {
                $inviteID = bin2hex(random_bytes(16));

                $statement = $this->app->db()->prepare('INSERT INTO `InviteCodes` (`InviteCode`) VALUES (:InviteCode)');
                $statement->execute(['InviteCode' => $inviteID]);
                
                $_SESSION['invite-id'] = $inviteID;
            }
        } elseif (isset($_POST['delident'])) {
            $validator = new FormValidator(array(
                'userid' => array('required' => true, 'regex' => '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/'),
                'identid' => array('required' => true, 'regex' => '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/')
            ));

            if ($validator->isValid($_POST)) {
                $os = new OpenSim($this->app->db());
                $identName = $os->getUserName($_POST['identid']);
                $userName = $os->getUserName($_POST['userid']);
                if ($os->deleteIdentity($_POST['userid'], $_POST['identid'])) {
                    $_SESSION['users-message'] = 'Identität <b>'.$identName.'</b> von <b>'.$userName.'</b> wurde gelöscht.';
                } else {
                    $_SESSION['users-message'] = 'Identität <b>'.$identName.'</b> konnte nicht gelöscht werden.';
                }
            }
        } else {
            $validator = new FormValidator(array(
                'userid' => array('required' => true, 'regex' => '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/')
            ));

            if ($validator->isValid($_POST)) {
                $opensim = new OpenSim($this->app->db());
                if (isset($_POST['genpw'])) {
                    $token = Util::generateToken(32);
                    $setToken = $this->app->db()->prepare('REPLACE INTO PasswordResetTokens(PrincipalID,Token,RequestTime) VALUES(?,?,?)');
                    $setToken->execute([$_POST['userid'], $token, time()]);
                    $resetLink = "https://".$this->app->config('domain').'/index.php?page=reset-password&token='.$token;

                    $_SESSION['users-message'] = 'Das Passwort für '.htmlspecialchars($opensim->getUserName($_POST['userid'])).' kann in den nächsten 24 Stunden über diesen Link zurückgesetzt werden: <b>'.$resetLink.'</b>';
                } elseif (isset($_POST['deluser'])) {
                    $name = $opensim->getUserName($_POST['userid']);
                    if ($opensim->deleteUser($_POST['userid'])) {
                        $_SESSION['users-message'] = 'Der Account <b>'.$name.'</b> wurde gelöscht.';
                    } else {
                        $_SESSION['users-message'] = 'Der Account <b>'.$name.'</b> konnte nicht gelöscht werden.';
                    }
                }
            }
        }

        header('Location: index.php?page=users');
    }
}
