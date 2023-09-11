<?php
declare(strict_types=1);

namespace Mcp\Api;

class DownloadIar extends \Mcp\RequestHandler
{

    public function get(): void
    {
        if (preg_match('/^[a-f0-9]{32}$/', $_GET['id'])) {
            $path = $this->app->getDataDir().DIRECTORY_SEPARATOR.'iars'.DIRECTORY_SEPARATOR.$_GET['id'].'.iar';
            if (file_exists($path)) {
                header('Content-Type: '.mime_content_type($path));
                header('Content-Disposition: attachment; filename='.$_GET['id'].'.iar');
                header('Content-Length: '.filesize($path));
                readfile($path);
                return;
            }
        }

        http_response_code(404);
    }
}
