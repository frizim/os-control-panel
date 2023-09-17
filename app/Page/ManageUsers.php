<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\OpenSim;
use Mcp\RequestHandler;
use Mcp\Util\Util;
use Mcp\Middleware\AdminMiddleware;
use Mcp\Util\TemplateVarArray;

class ManageUsers extends RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new AdminMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {
        // Only select current primary account
        $statement = $this->app->db()->prepare("SELECT FirstName,LastName,UserLevel,PrincipalID FROM UserAccounts JOIN auth ON auth.UUID = UserAccounts.PrincipalID ORDER BY Created ASC");
        $statement->execute();
    
        $statementIdent = $this->app->db()->prepare("SELECT FirstName,LastName,UserLevel,IdentityID FROM mcp_user_identities JOIN UserAccounts ON UserAccounts.PrincipalID = mcp_user_identities.IdentityID WHERE mcp_user_identities.PrincipalID = ? AND mcp_user_identities.PrincipalID != mcp_user_identities.IdentityID");
        $res = new TemplateVarArray();
        while ($row = $statement->fetch()) {
            $user = new TemplateVarArray();
            $user['firstName'] = $row["FirstName"];
            $user["lastName"] = $row["LastName"];
            $user["level"] = strval($row["UserLevel"]);
            $user["uuid"] = $row["PrincipalID"];
            $user["identities"] = new TemplateVarArray();
            $statementIdent->execute([$row['PrincipalID']]);
            while ($identRow = $statementIdent->fetch()) {
                $ident = new TemplateVarArray();
                $ident["firstName"] = $identRow["FirstName"];
                $ident["lastName"] = $identRow["LastName"];
                $ident["level"] = strval($identRow["UserLevel"]);
                $ident["uuid"] = $identRow["IdentityID"];
                $user["identities"][] = $ident;
            }
            $res[] = $user;
        }
    
        $tpl = $this->app->template('users.php')->parent('__dashboard.php')->vars([
            'title' => 'Benutzer',
            'username' => $_SESSION['DISPLAYNAME'],
            'users' => $res
            ]);

        if (isset($_SESSION['users-message'])) {
            $tpl->var('message', $_SESSION['users-message']);
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

                $statement = $this->app->db()->prepare('INSERT INTO `mcp_invites` (`InviteCode`) VALUES (:InviteCode)');
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
                    $setToken = $this->app->db()->prepare('REPLACE INTO mcp_password_reset(PrincipalID,Token,RequestTime) VALUES(?,?,?)');
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
