<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: switcher.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
class Switcher {
    public $selected = '';
    private $args = [];
    private $changed = FALSE;
    private $buttons = [];
    private $class;
    private $cookie;
    private $dir;
    private $enabled = TRUE;
    private $ext;
    private $mode;
    private $name;
    private $post;
    private $props = [];
    private $separator;
    private $default;

    public function __construct($mode, $dir, $ext, $default, $class = '', $separator = " ", $auto = TRUE, $args = '') {
        $this->args = $args;
        $this->class = $class;
        $this->cookie = $_COOKIE;
        $this->default = $default;
        $this->dir = THEME.$dir;
        $this->ext = $ext;
        $this->mode = $mode;
        $this->name = $dir;
        $this->post = $_POST;
        $this->separator = $separator;

        if ($auto) {
            $this->props = $this->getProps();
            $this->selected = $this->getSelected();
            if ($this->changed) {
                $this->writeSelected();
            }
        }
    }

    private function getProps() {
        $props = [];

        if ($this->mode == 'select') {
            $dirHandle = opendir($this->dir);
            if ($dirHandle) {
                while (FALSE !== ($file = readdir($dirHandle))) {
                    if (!is_dir($this->dir.'/'.$file) && preg_match("/[A-z0-9]+\.".$this->ext."\z/", $file)) {
                        $props[] = str_replace('.'.$this->ext, '', $file);
                    }
                }
            }
        } else if ($this->mode == 'increment') {
            $props = ['less', 'reset', 'more'];
        }

        return $props;
    }

    private function getSelected() {
        $cookie_val = isset($this->cookie['theme_'.$this->name]) ? $this->cookie['theme_'.$this->name] : '';

        if ($this->mode == 'select') {
            if (isset($this->post['change_'.$this->name])) {
                foreach ($this->props as $prop) {
                    if (isset($this->post[$prop.'_x'])) {
                        $this->changed = TRUE;

                        return $prop;
                    }
                }
            } else if (!empty($cookie_val)) {
                if (in_array($cookie_val, $this->props)) {
                    return $cookie_val;
                }
            }

            return $this->default;
        } else if ($this->mode == 'increment') {
            if (is_numeric($cookie_val) && !isset($this->post['reset_x'])) {
                $value = $cookie_val;
            } else {
                $value = $this->default;
            }
            if (isset($this->post['change_'.$this->name])) {
                $this->changed = TRUE;
                if (isset($this->post['less_x'])) {
                    if (!isset($this->args['min']) || $value + $this->args['step'] >= $this->args['min']) {
                        $value = $value - $this->args['step'];
                    }
                } else if (isset($this->post['more_x'])) {
                    if (!isset($this->args['max']) || $value + $this->args['step'] <= $this->args['max']) {
                        $value = $value + $this->args['step'];
                    }
                }
            }

            return $value;
        }

        return FALSE;
    }

    private function writeSelected() {
        if ($this->selected == $this->default) {
            setcookie('theme_'.$this->name, $this->selected, time() - 3600 * 24 * 14, '/');
        } else {
            setcookie('theme_'.$this->name, $this->selected, time() + 3600 * 24 * 14, '/');
        }
    }

    public function disable() {
        $this->enabled = FALSE;
        $this->selected = $this->default;
    }

    public function makeForm($class = '') {
        if ($this->enabled) {
            $this->buttons = $this->getButtons();

            $form = '<form id="theme_'.$this->name.'"'.(!empty($class) ? ' class="'.$class.'"' : '').' method="post" action="'.FUSION_REQUEST.'">';
            $form .= '<div>';
            $form .= '<input type="hidden" name="change_'.$this->name.'" value="1"/>';
            $form .= implode($this->separator, $this->buttons);
            $form .= '</div>';
            $form .= '</form>';

            return $form;
        }

        return FALSE;
    }

    private function getButtons() {
        foreach ($this->props as $prop) {
            if ($prop != $this->selected) {
                $this->buttons[] = '<input type="image" name="'.$prop.'" src="'.$this->dir.'/'.$prop.'.'.$this->ext.'" class="'.$this->class.'" alt="'.$prop.'"/>';
            }
        }

        return $this->buttons;
    }

    public function makeHeadTag() {
        add_to_head('<link rel="stylesheet" type="text/css" href="'.$this->dir.'/'.$this->selected.'.css"/>');
    }
}
