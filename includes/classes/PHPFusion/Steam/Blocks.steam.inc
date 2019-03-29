<?php

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

        $default = array(
            'title' => '',
            'meta' => '',
            'image_src' => '',
            'description' => '',
            'footer' => ''
        );

        $options += $default;

        $path = THEMES.'templates/boilers/'.$this->steam->get_boiler().'/html/panel.html';

        $tpl = \PHPFusion\Template::getInstance('box');

        $tpl->set_template( $path );

        if ($options['title']) $tpl->set_block('title', $options);

        if ($options['meta']) { $tpl->set_block('meta', $options); }

        if ($options['image_src']) $tpl->set_block('image_src', $options);

        if ($options['description']) $tpl->set_block('description', $options);

        if ($options['footer']) $tpl->set_block('footer', $options);

        return $this->steam->render($tpl);

    }

}