<?php

/**
 * Extend
 *
 * @package Less
 * @subpackage tree
 */
class Less_Tree_Extend extends Less_Tree {

    public $selector;
    public $option;
    public $index;
    public $selfSelectors = [];
    public $allowBefore;
    public $allowAfter;
    public $firstExtendOnThisSelectorPath;
    public $type = 'Extend';
    public $ruleset;


    public $object_id;
    public $parent_ids = [];

    /**
     * @param integer $index
     */
    public function __construct($selector, $option, $index) {
        static $i = 0;
        $this->selector = $selector;
        $this->option = $option;
        $this->index = $index;

        switch ($option) {
            case "all":
                $this->allowBefore = TRUE;
                $this->allowAfter = TRUE;
                break;
            default:
                $this->allowBefore = FALSE;
                $this->allowAfter = FALSE;
                break;
        }

        $this->object_id = $i++;
        $this->parent_ids = [$this->object_id];
    }

    public function accept($visitor) {
        $this->selector = $visitor->visitObj($this->selector);
    }

    public function compile($env) {
        Less_Parser::$has_extends = TRUE;
        $this->selector = $this->selector->compile($env);

        return $this;
        //return new Less_Tree_Extend( $this->selector->compile($env), $this->option, $this->index);
    }

    public function findSelfSelectors($selectors) {
        $selfElements = [];


        for ($i = 0, $selectors_len = count($selectors); $i < $selectors_len; $i++) {
            $selectorElements = $selectors[$i]->elements;
            // duplicate the logic in genCSS function inside the selector node.
            // future TODO - move both logics into the selector joiner visitor
            if ($i && $selectorElements && $selectorElements[0]->combinator === "") {
                $selectorElements[0]->combinator = ' ';
            }
            $selfElements = array_merge($selfElements, $selectors[$i]->elements);
        }

        $this->selfSelectors = [new Less_Tree_Selector($selfElements)];
    }

}
