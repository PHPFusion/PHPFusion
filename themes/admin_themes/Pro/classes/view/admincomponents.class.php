<?php

namespace PHPFusion\Pro\Classes\View;

class AdminComponents {

    private $tpldir = __DIR__ . '/../../templates/';

    /* Components aside open & collapse variation */
    public function openSide($value, $collapse = FALSE, $class = '') {
        echo fusion_render($this->tpldir,
                           'components.twig',
                           [
                               'macro'    => 'openside',
                               'value'    => $value,
                               'class'    => $class,
                               'collapse' => $collapse
                           ],
                           TRUE);
    }

    /* Components aside close */
    public function closeSide() {

        echo fusion_render($this->tpldir, 'components.twig', [ 'macro' => 'closeside' ], TRUE);
    }

    /* Components table open */
    public function openTable($value) {

        echo fusion_render($this->tpldir, 'components.twig', [ 'macro' => 'opentable', 'value' => $value ], TRUE);
    }

    /* Components table close */
    public function closeTable() {

        echo fusion_render($this->tpldir, 'components.twig', [ 'macro' => 'closetable' ], TRUE);
    }

    /* Components grid open */
    public function openGrid($count, $class = '') {

        echo fusioN_render($this->tpldir,
                           'components.twig',
                           [ 'macro' => 'opengrid', 'value' => $count, 'class' => $class ],
                           TRUE);
    }

    /* Components grid close */
    public function closeGrid() {

        echo fusioN_render($this->tpldir, 'components.twig', [ 'macro' => 'closegrid' ], TRUE);
    }

}
