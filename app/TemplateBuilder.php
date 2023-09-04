<?php
declare(strict_types=1);

namespace Mcp;

use Mcp\Util\TemplateVarArray;

class TemplateBuilder
{

    private string $basedir;
    private string $name;
    private ?string $parent = null;
    private array $vars = [];

    public function __construct(string $basedir, string $name)
    {
        $this->basedir = $basedir;
        $this->name = $name;
    }

    public function parent(string $parent): TemplateBuilder
    {
        $this->parent = $parent;
        return $this;
    }

    public function vars(array $vars): TemplateBuilder
    {
        foreach ($vars as $key => $val) {
            $this->vars[$key] = htmlspecialchars(strval($val));
        }
        return $this;
    }

    public function var(string $key, string $val): TemplateBuilder
    {
        $this->vars[$key] = htmlspecialchars($val);
        return $this;
    }

    public function unsafeVar(string $key, string $val): TemplateBuilder
    {
        $this->vars[$key] = $val;
        return $this;
    }

    public function render(): void
    {
        $v = new TemplateVarArray($this->vars);
        $basepath = $this->basedir.DIRECTORY_SEPARATOR;
        if ($this->parent == null) {
            require $basepath.$this->name;
        } else {
            $v['child-template'] = $basepath.$this->name;
            require $basepath.$this->parent;
        }
    }
}
