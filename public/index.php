<?php
declare(strict_types=1);

date_default_timezone_set("Europe/Berlin");
error_reporting(E_ALL);

$basedir = dirname(__DIR__);
require __DIR__ . '/../vendor/autoload.php';

(new \Mcp\Mcp($basedir))->handleRequest();
