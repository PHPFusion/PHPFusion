<?php

    function form_hidden($title, $input_name, $input_id, $input_value, $array=false) {
        $title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
        $html = "<input type='hidden' name='$input_name' id='$input_id' value='$input_value' readonly>";
        return $html;
    }



?>