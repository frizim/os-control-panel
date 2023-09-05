<?php
declare(strict_types=1);

namespace Mcp\Api;

use Mcp\OpenSim;

class ViewerWelcomePage extends \Mcp\RequestHandler
{
    public function get(): void
    {
        $images = array();
        if ($handle = opendir('./img/viewerWelcomeImages')) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $images[] = "./img/viewerWelcomeImages/".$entry;
                }
            }
        
            closedir($handle);
        }
    
        shuffle($images);

        $opensim = new OpenSim($this->app->db());

        $this->app->template('viewerWelcomeImages.php')->vars([
            'title' => 'Splash',
            'grid-name' => $this->app->config('grid')['name'],
            'news' => $this->app->config('grid')['main-news']
        ])->unsafeVar('json-image-array', json_encode($images))
            ->unsafeVar('image-1', $images[0])->unsafeVar('image-2', $images[1])
            ->unsafeVar('stats', "Registrierte User: ".$opensim->getUserCount()."<br>Regionen: ".$opensim->getRegionCount()."<br>Aktuell Online: ".($opensim->getOnlineCount()-1))
            ->render();
    }
}
