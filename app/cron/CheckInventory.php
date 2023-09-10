<?php
declare(strict_types=1);

namespace Mcp\Cron;

class CheckInventory extends CronJob
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, Frequency::MONTHLY);
    }

    public function run(): bool
    {
        $invCheckStatement = $this->app->db()->prepare("UPDATE inventoryitems i SET i.inventoryName = concat('[DEFEKT] ', i.inventoryName)
            WHERE i.assetID IN (
               SELECT i.assetID FROM inventoryitems i WHERE
                    NOT EXISTS(
                        SELECT * FROM fsassets fs WHERE fs.id = i.assetID)
                AND NOT i.inventoryName LIKE '[DEFEKT] %' AND i.assetType <> 24
            )");

        $invCheckStatement->execute();
        return true;
    }
}
