<?php
namespace PHPFusion\Steam;
/**
 * Class Blocks
 * Render a cross framework HTML blocks.
 */
class Blocks {

    private $steam; // pass the steam here.

    /**
     * Blocks constructor.
     *
     * @param \PHPFusion\Steam $steam
     */
    public function __construct(\PHPFusion\Steam $steam) {
        $this->steam = $steam;
    }

    public function box(array $options) {

        $default = [
            'title'       => '',
            'meta'        => '',
            'image_src'   => '',
            'description' => '',
            'footer'      => ''
        ];

        $options += $default;

        $path = $this->steam->get_BoilerPath()."html".DIRECTORY_SEPARATOR."panel.html";

        $tpl = \PHPFusion\Template::getInstance('box');

        $tpl->set_template($path);

        if ($options['title'])
            $tpl->set_block('title', $options);

        if ($options['meta']) {
            $tpl->set_block('meta', $options);
        }

        if ($options['image_src'])
            $tpl->set_block('image_src', $options);

        if ($options['description'])
            $tpl->set_block('description', $options);

        if ($options['footer'])
            $tpl->set_block('footer', $options);

        return $this->steam->render($tpl);

    }

    public function openside($title = '') {
        return fusion_get_function('openside', $title);
    }

    public function closeside() {
        return fusion_get_function('closeside');
    }

    public function opentable($title = '') {
        return fusion_get_function('opentable', $title);
    }

    public function closetable() {
        return fusion_get_function('closetable');
    }


}