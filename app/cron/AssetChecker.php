<?php
declare(strict_types=1);

namespace Mcp\Cron;

class AssetChecker extends CronJob
{

    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, Frequency::MONTHLY);
    }

    public function run(): bool
    {
        $statement = $this->app->db()->prepare("SELECT id,hash FROM fsassets ORDER BY create_time DESC");
        $statement->execute();

        $count = 0;

        while ($row = $statement->fetch()) {
            $fileNameParts = array();
            $fileNameParts[0] = substr($row['hash'], 0, 2);
            $fileNameParts[1] = substr($row['hash'], 2, 2);
            $fileNameParts[2] = substr($row['hash'], 4, 2);
            $fileNameParts[3] = substr($row['hash'], 6, 4);
            $fileNameParts[4] = $row['hash'] . ".gz";

            $fileNameParts['UUID'] = $row['id'];
            $fileNameParts['FilePath'] = "/data/assets/base/" . $fileNameParts[0] . "/" . $fileNameParts[1] . "/" . $fileNameParts[2] . "/" . $fileNameParts[3] . "/" . $fileNameParts[4];

            if (file_exists($fileNameParts['FilePath'])) {
                $filesize = filesize($fileNameParts['FilePath']);
                if ($filesize === false) {
                    continue;
                }
            } else {
                $filesize = 0;
            }

            $fileNameParts['FileSize'] = $filesize;
            $fileNameParts['Count'] = $count++;

            if ($fileNameParts['FileSize'] == 0) {
                $add = $this->app->db()->prepare('DELETE FROM fsassets WHERE hash = :fileHash');
                $add->execute(['fileHash' => $row['hash']]);
            }
        }
        return true;
    }
}
