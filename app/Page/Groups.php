<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\OpenSim;
use Mcp\Middleware\LoginRequiredMiddleware;
use Mcp\Util\TemplateVarArray;

class Groups extends \Mcp\RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new LoginRequiredMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {
        $opensim = new OpenSim($this->app->db());

        $statementGroups = $this->app->db()->prepare("SELECT Name,FounderID,os_groups_membership.GroupID FROM os_groups_groups JOIN os_groups_membership ON os_groups_groups.GroupID = os_groups_membership.GroupID WHERE PrincipalID = ?");
        $statementGroups->execute(array($_SESSION['UUID']));
    
        $res = new TemplateVarArray();
        while ($rowGroups = $statementGroups->fetch()) {
            $group = new TemplateVarArray();
            $group["name"] = $rowGroups["Name"];
            $group["founder"] = $opensim->getUserName($rowGroups['FounderID']);
            $group["uuid"] = $rowGroups["GroupID"];
            $res[] = $group;
        }
    
        $this->app->template('groups.php')->parent('__dashboard.php')->vars([
            'title' => 'Gruppen',
            'username' => $_SESSION['DISPLAYNAME'],
            'groups' => &$res
        ])->render();
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
