<?php
namespace PHPFusion;

/**
 * Class Steam
 * A class to compose, convert and compose boilerplate components.
 *
 * @package PHPFusion
 */
class Steam {

    private $boilers = 'bootstrap3';

    public function __construct($boilers ='') {

        // we will need a default framework selector in fusion_get_settings('boiler') which value can be hosted as 'semantic', 'foundation', 'bootstrap3', 'bootstrap4'
        // drop $settings['bootstrap']
        if ($boilers) {
            $this->boilers = $boilers;
        }

    }

    /**
     * Override boilers.
     * @param $boilers
     */
    public function set_boiler($boilers) {
        $this->boilers = $boilers;
    }

    /**
     * Run the boiler.
     */
    public function run() {
        require_once THEMES.'templates/boilers/'.$this->boilers.'/package.php';
    }


    /**
     * @return string
     */
    public function get_boiler() {
        return $this->boilers;
    }

    /**
     * @param $command
     *
     * @return object
     */
    public function load($command) {
        $command_name = lcfirst(strtolower($command));
        require_once __DIR__.'/Steam/'.$command_name.'.steam.inc';

        return new $command_name( $this );
    }

    /**
     * @param $tpl
     *
     * @return string
     */
    public function render($tpl) {
        return $tpl->get_output();
    }

}