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
                if ($entry != '.' && $entry != '..') {
                    $images[] = "/img/viewerWelcomeImages/$entry";
                }
            }
        
            closedir($handle);
        }
    
        shuffle($images);

        $opensim = new OpenSim($this->app->db());

        $this->app->template('viewerWelcomeImages.php')->vars([
            'title' => 'splash.title',
            'grid-name' => $this->app->config('grid')['name'],
            'news' => $this->app->config('grid')['main-news'],
            'registered' => $opensim->getUserCount(),
            'regions' => $opensim->getRegionCount(),
            'online' => $opensim->getOnlineCount() - 1,
            'images' => $images
        ])->render();
    }
}
