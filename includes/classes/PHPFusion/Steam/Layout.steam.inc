<?php

/**
 * Class Blocks
 * Render a cross framework HTML blocks.
 */
class Layout {

    private $steam; // pass the steam here.

    /**
     * Blocks constructor.
     *
     * @param \PHPFusion\Steam $steam
     */
    public function __construct(\PHPFusion\Steam $steam) {
        $this->steam = $steam;
    }

    public function grid(array $options) {

        $default = array(
            array(
                'content' => '',
                'class' => '', // supports % or px
                'xs' => 12,
                'sm' => 12,
                'md' => 12,
                'lg' => 12,
            )
        );
        $options += $default;

        $tpl = \PHPFusion\Template::getInstance('grid');

        $tpl->set_template(THEMES.'templates/boilers/'.$this->steam->get_boiler().'/html/grid.html');

        foreach($options as $grid) {

            if (empty($grid['class'])) {
                switch($this->steam->get_boiler()) {
                    case 'bootstrap3':
                        $grid['class'] = $this->get_bootstrap_class($grid);
                        break;
                    default:
                }
            }

            $tpl->set_block('column', $grid);

        }

        return (string)$this->steam->render($tpl);

    }

    public function get_bootstrap_class($grid) {
        // use implode and run foreach.
        $array = array('xs', 'sm', 'md', 'lg');
        $class = array();
        foreach($array as $grid_size) {
            if (!empty($grid[ $grid_size ])) {
                $class[] = 'col-'.$grid_size.'-'.$grid[ $grid_size];
            }
        }
        return implode(' ', $class);
    }

}