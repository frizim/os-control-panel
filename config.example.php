<?php
$RUNTIME['PDO']                     =   new PDO('mysql:host=...;dbname=...', '...', '...');

$RUNTIME['GRID']['NAME']            = "OpenSim";
$RUNTIME['GRID']['MAIN_NEWS']       = "Yet an other OpenSim Grid.";
$RUNTIME['GRID']['HOMEURL']         = "http://...:8002";

$RUNTIME['SMTP']['SERVER']          =   "localhost";
$RUNTIME['SMTP']['PORT']            =   25;
$RUNTIME['SMTP']['ADDRESS']         =   "noreply@localhost";
$RUNTIME['SMTP']['NAME']            =   "4Creative";
$RUNTIME['SMTP']['PASS']            =   "...";

$RUNTIME['TOOLS']['IMAGESERVICE']   =   "https://image-service.4creative.net/";
$RUNTIME['TOOLS']['TOS']            =   "https://4creative.net/nutzung.html";

$RUNTIME['DEFAULTAVATAR']["AVATAR1"]['UUID']    =   "0817c915-293a-4041-b5a4-c7c53666bcc6";

$RUNTIME['SIDOMAN']['URL']          = "https://sidoman.4creative.net/";
$RUNTIME['SIDOMAN']['CONTAINER']    = "...";
$RUNTIME['SIDOMAN']['PASSWORD']     = "...";

$RUNTIME['DOMAIN']                  = "mcp.4creative.net";
$RUNTIME['IAR']['BASEURL']          = "https://mcp.4creative.net/data/";

$RUNTIME['PASSWORD_MIN_LENGTH']     = 8;

$RUNTIME['CRON_RESTRICTION'] = 'key';
$RUNTIME['CRON_KEY'] = 'changeme';
