<?php

namespace PHPFusion\Jupiter\Classes\View;

class AdminComponents {

    /* Components aside open & collapse variation */
    public function openSide($value, $collapse = FALSE, $class = '') {
        echo $this->renderComponent('openside', [
            'value'    => $value,
            'class'    => $class,
            'collapse' => $collapse
        ]);
    }

    /* Components aside close */
    public function closeSide() {
        echo $this->renderComponent('closeside');
    }

    /* Components table open */
    public function openTable($value) {
        echo $this->renderComponent('opentable', ['value' => $value]);
    }

    /* Components table close */
    public function closeTable() {
        echo $this->renderComponent('closetable');
    }

    /* Components grid open */
    public function openGrid($count, $class = '') {
        echo $this->renderComponent('opengrid', ['value' => $count, 'class' => $class]);
    }

    /* Components grid close */
    public function closeGrid() {
        echo $this->renderComponent('closegrid');
    }

    private function renderComponent(string $component, array $info = []): string {
        if (function_exists('fusion_render_framework_component')) {
            $rendered = fusion_render_framework_component($component, $info);
            if ($rendered !== '') {
                return $rendered;
            }
        }

        require_once __DIR__.'/../../templates/components.tpl.php';

        return pro_admin_component_tpl($component, $info);
    }

}
