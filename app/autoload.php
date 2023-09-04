<?php
namespace Mcp;

class Autoloader {

    private string $appPath;
    private string $libPath;

    public function __construct($basedir)
    {
        $this->appPath = $basedir.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR;
        $this->libPath = $basedir.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;
    }

    public function load($className) {
        $parts = explode('\\', $className);
        $len = count($parts);

        $res = $parts[0] === 'Mcp' ? $this->appPath : $this->libPath;
        for ($i = 1; $i < $len - 1; $i++) {
            $res = $res.strtolower($parts[$i]).DIRECTORY_SEPARATOR;
        }

        require $res.$parts[$len - 1].'.php';
    }

}
