<?php
date_default_timezone_set("Europe/Berlin");
error_reporting(E_ALL);
include_once("config.php");
$RUNTIME['BASEDIR'] = __DIR__;
set_include_path('.:'.$RUNTIME['BASEDIR']);

session_set_cookie_params([
	'lifetime' => 86400,
	'path' => '/',
	'domain' => $RUNTIME['DOMAIN'],
	'httponly' => true,
	'secure' => true,
	'samesite' => 'Lax'
]);

session_start();
if(!isset($_SESSION['csrf']) || strlen($_SESSION['csrf']) != 64) {
	$_SESSION['csrf'] = bin2hex(random_bytes(32));
}

include_once("app/utils.php");
include_once("app/HTML.php");

function isValidEndpoint(string $pageName, string $dirPrefix) {
	return preg_match('/^[a-zA-Z0-9\.]{1,100}$/', $pageName) && file_exists("./".$dirPrefix."/".$pageName.".php");
}

function needsLogin(?string $pageName) {
	return $pageName != 'register' && $pageName != 'forgot' && $pageName != 'reset-password' && $pageName != 'login';
}

//TODO: add API keys and/or rate limiting
if(isset($_GET['api'])) {
	if(isValidEndpoint($_GET['api'], 'api')) {
		include "./api/".$_GET['api'].".php";
	} else {
		die("ERROR; ENDPOINT NOT EXIST");
	}

	die();
}

if ($handle = opendir('./plugins/')) {
	while (false !== ($entry = readdir($handle))) {
		if ($entry != "." && $entry != "..") {
			include_once "./plugins/".$entry;
		}
	}

	closedir($handle);
}

if(isset($_GET['logout']) && $_GET['logout'] == '1') {
	$_SESSION = array();
	header('Location: index.php');
}

if(isset($_SESSION['LOGIN']) && $_SESSION['LOGIN'] == 'true') {
	if(!isset($_GET['page'])) {
		include './pages/dashboard.php';
	} else if(isValidEndpoint($_GET['page'], 'pages')) {
		include "./pages/".$_GET['page'].".php";
	} else {
		include "./pages/error.php";
	}
	
	die();
}
else {
	$page = isset($_GET['page']) ? $_GET['page'] : 'login';

	if(needsLogin($page)) {
		$_SESSION['loginMessage'] = 'Du musst dich einloggen, um das MCP nutzen zu können';
		$_SESSION['loginMessageColor'] = 'red';
		header('Location: index.php?page=login');
	}
	else {
		include "./pages/".$page.".php";
	}
}

?>