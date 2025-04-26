<?php
declare(strict_types=1);

namespace Mcp;

use Mcp\Util\TemplateVarArray;
use MessageFormatter;
use ResourceBundle;

class I18n
{

    private ResourceBundle $res;
    private string $lang;

    const LANGS = [ 'de', 'en' ];

    public function __construct(string $localeDir, ?string $preferred = null)
    {
        if($preferred) {
            $this->lang = $preferred;
        }
        else {
            $lang = 'en';
            if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $preferred = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
                if(in_array($preferred, I18n::LANGS)) {
                    $lang = $preferred;
                }
            }

            $this->lang = $lang;
        }

        putenv("LANG=$this->lang");
        $this->res = ResourceBundle::create($this->lang, $localeDir);
    }

    public function t(string $id, TemplateVarArray|array|null $params = null) {
        $pattern = $this->res->get($id, true);
        if(!$pattern) {
            error_log("Message $id not found in resoure bundle");
            $pattern = $id;
        }

        if($params instanceof TemplateVarArray) {
            $params = $params->toArray();
        }

        return nl2br(MessageFormatter::formatMessage($this->lang, $pattern, $params ? $params : []));
    }

}
