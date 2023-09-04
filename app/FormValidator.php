<?php
declare(strict_types=1);

namespace Mcp;

class FormValidator {

    private array $fieldValidation;

    public function __construct(array $fieldValidation)
    {
        $this->fieldValidation = $fieldValidation;
    }

    public function isValid(array $req): bool
    {
        if (!isset($req['csrf']) || $req['csrf'] !== $_SESSION['csrf']) {
            return false;
        }

        foreach ($this->fieldValidation as $field => $params) {
            if (isset($req[$field]) && strlen(trim($req[$field])) > 0) {
                if (isset($params['regex'])) {
                    if (!preg_match($params['regex'], $req[$field])) {
                        return false;
                    }
                }
                elseif (isset($params['equals']) && $params['equals'] !== $req[$field]) {
                    return false;
                }
            }
            elseif (isset($params['required']) && $params['required']) {
                return false;
            }
        }

        return true;
    }

}
