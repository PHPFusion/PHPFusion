<?php

/**
 * Variable
 *
 * @package Less
 * @subpackage tree
 */
class Less_Tree_Variable extends Less_Tree {

    public $name;
    public $index;
    public $currentFileInfo;
    public $evaluating = FALSE;
    public $type = 'Variable';

    /**
     * @param string $name
     */
    public function __construct($name, $index = NULL, $currentFileInfo = NULL) {
        $this->name = $name;
        $this->index = $index;
        $this->currentFileInfo = $currentFileInfo;
    }

    public function compile($env) {

        if ($this->name[1] === '@') {
            $v = new Less_Tree_Variable(substr($this->name, 1), $this->index + 1, $this->currentFileInfo);
            $name = '@'.$v->compile($env)->value;
        } else {
            $name = $this->name;
        }

        if ($this->evaluating) {
            throw new Less_Exception_Compiler("Recursive variable definition for ".$name, NULL, $this->index, $this->currentFileInfo);
        }

        $this->evaluating = TRUE;

        foreach ($env->frames as $frame) {
            if ($v = $frame->variable($name)) {
                $r = $v->value->compile($env);
                $this->evaluating = FALSE;

                return $r;
            }
        }

        throw new Less_Exception_Compiler("variable ".$name." is undefined in file ".$this->currentFileInfo["filename"], NULL, $this->index);
    }

}
