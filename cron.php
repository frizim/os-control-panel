<?php
$RUNTIME = array();
$RUNTIME['BASEDIR'] = __DIR__;
set_include_path('.:'.$RUNTIME['BASEDIR']);
include_once "config.php";

if(!isset($RUNTIME['CRON_RESTRICTION'])) {
    http_response_code(500);
    die();
}

if ($RUNTIME['CRON_RESTRICTION'] != 'none' && (!isset($RUNTIME['CRON_KEY']) || !isset($REQUEST['key']) || $_REQUEST['key'] !== $RUNTIME['CRON_KEY'])) {
    http_response_code(401);
    die();
}

if ($handle = opendir('./cron/')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            include_once "./cron/".$entry;
        }
    }
    closedir($handle);
}
