<?php
namespace PHPFusion\Steam;

use PHPFusion\Steam;

/**
 * Class Modal
 *
 * @package PHPFusion\Steam
 */
class Modal {

    private $steam; // pass the steam here.

    /**
     * Layout constructor.
     */
    public function __construct() {
        $this->steam = Steam::getInstance();
        require_once $this->steam->getBoilerPath().DIRECTORY_SEPARATOR."modal.php";
    }

    /**
     * @param $id
     * @param $title
     * @param $options
     */
    public function openmodal($id, $title, $options) {
        $modal = new \Modal();
        return $modal->openmodal($id, $title, $options);
    }

    /**
     * @param string $content
     * @param bool   $dismiss
     *
     * @return string
     */
    public function modalfooter($content = "", $dismiss = FALSE) {
        $modal = new \Modal();
        return $modal->modalfooter($content, $dismiss);
    }

    /**
     * @return string
     */
    public function closemodal() {
        $modal = new \Modal();
        return $modal->closemodal();
    }

}
