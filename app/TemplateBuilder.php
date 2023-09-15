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
            $this->vars[$key] = htmlspecialchars(strval($val));
        }
        return $this;
    }

    /**
     * Sets the specified variable for this template, after escaping it.
     */
    public function var(string $key, string $val): TemplateBuilder
    {
        $this->vars[$key] = htmlspecialchars($val);
        return $this;
    }

    /**
     * Sets the specified variable for this template WITHOUT escaping it.
     *
     * User input included this way has to be manually sanitized before.
     */
    public function unsafeVar(string $key, string $val): TemplateBuilder
    {
        $this->vars[$key] = $val;
        return $this;
    }

    /**
     * Displays the template(s) with the current set of variables.
     */
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
