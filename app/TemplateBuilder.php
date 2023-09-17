<?php
declare(strict_types=1);

namespace Mcp;

use Mcp\Util\TemplateVarArray;

class TemplateBuilder
{

    private string $basedir;
    private string $name;
    private ?string $parent = null;
    private TemplateVarArray $vars;
    private string $csrf;

    public function __construct(string $basedir, string $name, string $csrf = "")
    {
        $this->basedir = $basedir;
        $this->name = $name;
        $this->vars = new TemplateVarArray([]);
        $this->csrf = $csrf;
    }

    /**
     * Sets another template to be the "parent" of this one.
     *
     * The template specified in this TemplateBuilder's constructor will be included into the parent.
     */
    public function parent(string $parent): TemplateBuilder
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Sets multiple variables after escaping them.
     */
    public function vars(array $vars): TemplateBuilder
    {
        foreach ($vars as $key => $val) {
            $this->vars[$key] = $val;
        }
        return $this;
    }

    /**
     * Sets the specified variable for this template, which will be automatically escaped on output.
     */
    public function var(string $key, string|TemplateVarArray $val): TemplateBuilder
    {
        $this->vars[$key] = $val;
        return $this;
    }

    /**
     * Displays the template(s) with the current set of variables.
     */
    public function render(): void
    {
        $v = &$this->vars;
        $csrf = $this->csrf;
        $basepath = $this->basedir.DIRECTORY_SEPARATOR;
        if ($this->parent == null) {
            require $basepath.$this->name;
        } else {
            $v['child-template'] = $basepath.$this->name;
            require $basepath.$this->parent;
        }
    }
}
