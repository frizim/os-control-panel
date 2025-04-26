<?php
declare(strict_types=1);

namespace Mcp;

use Mcp\Util\TemplateVarArray;
use Mcp\I18n;


class TemplateBuilder
{

    private string $basedir;
    private string $name;
    private ?string $parent = null;
    private TemplateVarArray $vars;
    private I18n $i18n;

    public function __construct(string $basedir, string $name, ?string $language = null)
    {
        $this->basedir = $basedir;
        $this->name = $name;
        $this->vars = new TemplateVarArray([]);
        $this->i18n = new I18n($this->basedir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'locales', $language);
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

    public function getI18n(): I18n {
        return $this->i18n;
    }

    /**
     * Displays the template(s) with the current set of variables.
     */
    public function render(): void
    {
        global $i18n;

        $v = &$this->vars;
        $csrf = '<input type="hidden" name="csrf" value="'.(isset($_SESSION['csrf']) ? $_SESSION['csrf'] : '').'">';
        $i18n = $this->i18n;
        $t = function(string $id, TemplateVarArray|array|null $vars = null) {
            global $i18n;
            return $i18n->t($id, is_array($vars) ? new TemplateVarArray($vars) : $vars);
        };

        $basepath = $this->basedir.DIRECTORY_SEPARATOR;
        if ($this->parent == null) {
            require $basepath.$this->name;
        } else {
            $v['child-template'] = $basepath.$this->name;
            require $basepath.$this->parent;
        }
    }
}
