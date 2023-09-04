<?php
declare(strict_types=1);

date_default_timezone_set("Europe/Berlin");
error_reporting(E_ALL);

$basedir = dirname(__DIR__);
require $basedir.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'autoload.php';
spl_autoload_register(array(new \Mcp\Autoloader($basedir), 'load'));

(new \Mcp\Mcp($basedir))->handleRequest();
