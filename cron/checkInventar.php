<?php
$InventarCheckStatement = $RUNTIME['PDO']->prepare("UPDATE inventoryitems i SET
i.inventoryName = concat('[DEFEKT] ', i.inventoryName)
WHERE
i.assetID IN (
               SELECT
                i.assetID
               FROM inventoryitems i
               WHERE
                    NOT EXISTS( SELECT *
                                FROM fsassets fs
                                WHERE
                                 fs.id = i.assetID
                              )
                AND NOT i.inventoryName LIKE '[DEFEKT] %'
                AND i.assetType <> 24
            )");

$InventarCheckStatement->execute();
