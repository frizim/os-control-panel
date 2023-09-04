<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\OpenSim;
use Mcp\Middleware\LoginRequiredMiddleware;

class Groups extends \Mcp\RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new LoginRequiredMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {
        $opensim = new OpenSim($this->app->db());

        $table = '<table class="table"><thead><tr><th scope="col">Name</th><th scope="col">Gründer</th><th scope="col">Aktionen</th></thead><tbody>';
        
        $statementGroups = $this->app->db()->prepare("SELECT Name,FounderID,os_groups_membership.GroupID FROM os_groups_groups JOIN os_groups_membership ON os_groups_groups.GroupID = os_groups_membership.GroupID WHERE PrincipalID = ?");
        $statementGroups->execute(array($_SESSION['UUID']));
    
        $csrf = $this->app->csrfField();
        while ($rowGroups = $statementGroups->fetch()) {
            $table = $table.'<tr><td>'.htmlspecialchars($rowGroups['Name']).'</td><td>'.htmlspecialchars($opensim->getUserName($rowGroups['FounderID'])).'</td><td><form action="index.php?page=groups" method="post">'.$csrf.'<input type="hidden" name="group" value="'.htmlspecialchars($rowGroups['GroupID']).'"><button type="submit" name="leave" class="btn btn-danger btn-sm">VERLASSEN</button></form></td></tr>';
        }
    
        $this->app->template('__dashboard.php')->vars([
            'title' => 'Gruppen',
            'username' => $_SESSION['DISPLAYNAME']
        ])->unsafeVar('child-content', $table.'</tbody></table>')->render();
    }

    public function post(): void
    {
        if (isset($_POST['leave'])) {
            $validator = new FormValidator(array(
                'group' => array('required' => true, 'regex' => '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/')
            ));

            if ($validator->isValid($_POST)) {
                $statementMembership = $this->app->db()->prepare("DELETE FROM os_groups_membership WHERE GroupID = ? AND PrincipalID = ?");
                $statementMembership->execute(array($_REQUEST['group'], $_SESSION['UUID']));
            }
        }

        header('Location: index.php?page=groups');
    }
}
