<?php
function form_label($title, $array = FALSE) {
    if (isset($title) && ($title !== "")) {
        $title = stripinput($title);
    } else {
        $title = "";
    }
    if (!is_array($array)) {
        $class = "";
        $icon  = "";
    } else {
        $class = (array_key_exists('class', $array)) ? $array['class'] : "";
        $icon  = (array_key_exists('icon', $array)) ? "<i class='".$array['icon']."'></i>" : "";
    }
    return "<span class='label $class'>$icon $title</span>\n";
}

function form_badge($title, $array = FALSE) {
    if (!is_array($array)) {
        $class = "";
        $icon = "";
    } else {
        $class = (array_key_exists('class', $array)) ? $array['class'] : "";
        $icon = (array_key_exists('icon', $array)) ? "<i class='".$array['icon']."'></i>" : "";
    }
    if (isset($title) && ($title !== "")) {
        $title = stripinput($title);
    } else {
        $title = "";
    }
    return "<span class='badge $class'>$icon $title</span>\n";
}

?>