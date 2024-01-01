<?php

namespace MofgForm;

/**
 * Support both snake_case() and camelCase()
 */
trait MultiMethodNameTrait
{
    public function __call($name, $arguments)
    {
        $snakeCaseName = $this->snakeCaseName($name);
        if(method_exists($this, $snakeCaseName)) {
            return call_user_func_array([$this, $snakeCaseName], $arguments);
        } else {
            $method = get_class($this)."::".$name."()";
            trigger_error("Error: Call to undefined method {$method}", E_USER_ERROR);
        }
    }

    private function snakeCaseName($camelCaseName)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $camelCaseName)), '_');
    }
}
