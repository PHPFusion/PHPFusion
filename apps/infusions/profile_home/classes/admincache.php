<?php
namespace PHPFusion\Infusions\Profile_Home\Classes;

class AdminCache {
    
    public function __construct() {
    }
    
    private $class;
    
    /**
     * @param $class
     *
     * @return $this
     */
    public function setClass( object $class ) {
        $this->class = $class;
        
        return $this;
    }
    
    /**
     * @param string $method
     *
     * @return false|string
     */
    public function cache( string $method ) {
        
        if ( is_callable( [ $this->class, $method ] ) ) {
            ob_start();
            $this->class->$method();
            return ob_get_clean();
        }
        
        return 'There are no pages to display';
    }
    
    
}
