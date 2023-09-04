<?php
declare(strict_types=1);

namespace Mcp\Page;

use Mcp\FormValidator;
use Mcp\OpenSim;
use Mcp\Middleware\LoginRequiredMiddleware;

class Friends extends \Mcp\RequestHandler
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, new LoginRequiredMiddleware($app, $app->config('domain')));
    }

    public function get(): void
    {
        $table = '<table class="table"><thead><tr><th scope="col">Name</th><th scope="col">Optionen</th></thead><tbody>';
        
        $statement = $this->app->db()->prepare("SELECT PrincipalID,Friend FROM Friends WHERE PrincipalID = ? ORDER BY Friend ASC");
        $statement->execute([$_SESSION['UUID']]);
    
        $opensim = new OpenSim($this->app->db());
    
        $csrf = $this->app->csrfField();
        while ($row = $statement->fetch()) {
            $friendData = explode(";", $row['Friend']);
            $friend = $friendData[0];
    
            $name = trim($opensim->getUserName($friend));
            if (count($friendData) > 1) {
                $friendData[1] = str_replace("http://", "", $friendData[1]);
                $friendData[1] = str_replace("https://", "", $friendData[1]);
                $friendData[1] = str_replace("/", "", $friendData[1]);
                $name = $name.' @ '.strtolower($friendData[1]);
            }
    
            $table = $table.'<tr><td>'.htmlspecialchars($name).'</td><td><form action="index.php?page=friends" method="post">'.$csrf.'<input type="hidden" name="uuid" value="'.htmlspecialchars($row['Friend']).'"><button type="submit" name="remove" class="btn btn-danger btn-sm">LÖSCHEN</button></form></td></tr>';
        }
    
        $this->app->template('__dashboard.php')->vars([
            'title' => 'Deine Freunde',
            'username' => $_SESSION['DISPLAYNAME']
        ])->unsafeVar('child-content', $table.'</tbody></table>')->render();
    }

    public function post(): void
    {
        if (isset($_POST['remove'])) {
            $validator = new FormValidator(array(
                'uuid' => array('required' => true, 'regex' => '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/')
            ));

            if ($validator->isValid($_POST)) {
                $statementMembership = $this->app->db()->prepare("DELETE FROM Friends WHERE Friend = ? AND PrincipalID = ?");
                $statementMembership->execute(array($_REQUEST['uuid'], $_SESSION['UUID']));
        
                $statementMembership = $this->app->db()->prepare("DELETE FROM Friends WHERE PrincipalID = ? AND Friend = ?");
                $statementMembership->execute(array($_REQUEST['uuid'], $_SESSION['UUID']));
            }
        }

        header('Location: index.php?page=friends');
    }
}
