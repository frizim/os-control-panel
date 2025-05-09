<?php
declare(strict_types=1);

namespace Mcp;

use Exception;
use PDO;

class Mcp implements ConnectionProvider
{

    private ?PDO $db = null;
    private array $config;
    private string $dataDir;
    private string $templateDir;

    const ROUTES = [
        'api' => [
            'onlineDisplay' => 'Api\\OnlineDisplay',
            'viewerWelcomeSite' => 'Api\\ViewerWelcomePage',
            'runCron' => 'Api\\CronStarter',
            'downloadIar' => 'Api\\DownloadIar'
        ],
        'page' => [
            'dashboard' => 'Page\\Dashboard',
            'forgot' => 'Page\\ForgotPassword',
            'friends' => 'Page\\Friends',
            'groups' => 'Page\\Groups',
            'identities' => 'Page\\Identities',
            'login' => 'Page\\Login',
            'profile' => 'Page\\Profile',
            'regions' => 'Page\\Regions',
            'register' => 'Page\\Register',
            'reset-password' => 'Page\\ResetPassword',
            'user-online-state' => 'Page\\OnlineUsers',
            'users' => 'Page\\ManageUsers'
        ]
    ];

    public function __construct($basedir)
    {
        $this->templateDir = $basedir.DIRECTORY_SEPARATOR.'templates';
        $this->dataDir = $basedir.DIRECTORY_SEPARATOR.'data';
        $this->config = array();
        try {
            $config = parse_ini_file($basedir.DIRECTORY_SEPARATOR.'config.ini', true);
            foreach ($config['general'] as $key => $val) {
                $this->config[$key] = $val;
            }
            unset($config['general']);
            $this->config = array_merge($config, $this->config);
        } catch (Exception $e) {
            error_log('Could not load config, aborting. Error: '.$e->getMessage());
            http_response_code(503);
            exit();
        }

        $migrate = new MigrationManager($basedir.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'migrate_ver');
        if (!$migrate->isMigrated() && !$migrate->migrate($this->db())) {
            error_log('Migration to latest DB structure version failed, aborting.');
            http_response_code(503);
            exit();
        }
    }

    /**
     * Connects to the MySQL database (if not done already) and returns the connection.
     */
    public function db(): PDO
    {
        if ($this->db == null) {
            $this->db = new PDO('mysql:host='.$this->config['mysql']['host'].';dbname='.$this->config['mysql']['db'],
                $this->config['mysql']['user'],
                $this->config['mysql']['password']);
        }

        return $this->db;
    }

    /**
     * Returns the value associated with the specified key in this config, as either a string, an integer or an array.
     * Keys are lower-cased for compatibility reasons.
     *
     * If there is no entry with this key, an empty array is returned.
     */
    public function config($key): string|array|int
    {
        $realKey = strtolower($key);
        return isset($this->config[$realKey]) ? $this->config[$realKey] : array();
    }

    /**
     * Creates a TemplateBuilder instance for the specified template file, setting some basic variables.
     */
    public function template($name): TemplateBuilder
    {
        return (new TemplateBuilder($this->templateDir, $name))->vars([
            'domain' => $this->config['domain'],
            'admin' => isset($_SESSION['LEVEL']) && $_SESSION['LEVEL'] > 100
        ]);
    }

    /**
     * Returns the path of the data/ directory, mostly used for dynamically created assets.
     */
    public function getDataDir(): string
    {
        return $this->dataDir;
    }

    public function handleRequest()
    {
        $reqClass = 'Mcp\\Page\\Error';
        if (empty($_GET)) {
            $reqClass = 'Mcp\\'.$this::ROUTES['page'][array_key_first($this::ROUTES['page'])];
        } else {
            if (isset($_GET['logout'])) {
                session_start();
                session_destroy();
                header('Location: /');
                return;
            }

            foreach ($this::ROUTES as $type => $routes) {
                if (isset($_GET[$type])) {
                    if (strlen($_GET[$type]) <= 100 && preg_match('/^[0-9a-zA-Z-_.]+$/', $_GET[$type]) && isset($routes[$_GET[$type]])) {
                        $reqClass = 'Mcp\\'.$routes[$_GET[$type]];
                    }
                    break;
                }
            }
        }

        (new $reqClass($this))->handleRequest();
    }
}
