<?php

    /* do the split into button */
    /*
    function form_buttons($title,$input_name, $input_id, $input_value, $array=false)
    {
        if (!is_array($array)) {
            $anchor = 0;
            $class = "small";
            $icon = "";
            $icon_stack = 0;
            $split = 0;
            $split_class = "btn-sm btn-default";
            $split_icon = "";
            $block = 0;
            $btn_block = "";
        } else {
            $anchor = (array_key_exists("anchor", $array) && ($array['anchor']==1)) ? 1 : 0;
            $class = (array_key_exists("class", $array) && ($array['class'] !=="")) ? $array['class'] : "small";
            $block = (array_key_exists("block", $array) && ($array['block'] == 1)) ? 1 : 0;
            $btn_block = ($block == 1) ? "btn-block" : "";
            $icon = (array_key_exists("icon", $array) && ($array['icon'] !=="")) ? "<i class='".$array['icon']."'></i>" : "";
            $icon_stack = (array_key_exists("icon_stack", $array) && isnum($array['icon_stack']) && ($array['icon_stack'] == 1)) ? 1 : 0;

            /** array listing
             * split = to enable split mode
             * split_class = for 2nd button styling
             * split_icon = maybe need icon?
             * split_options = full drop down list with array as href, value as description
             *
            $split = (array_key_exists("split",$array) && $array['split'] == 1)  ? 1 : 0;
            $split_class = (array_key_exists('split_class', $array)) ? $array['split_class'] : "btn-sm btn-default";
            $split_icon = (array_key_exists('split_icon', $array)) ? "<i class='".$array['split_icon']."'></i>" : "";
            $split_title = (array_key_exists('split_title', $array)) ? $array['split_title'] : "";
            $split_name = (array_key_exists('split_name', $array)) ? $array['split_name'] : "";
            $split_link = (array_key_exists('split_link', $array)) ? $array['split_link'] : "";

        } //end array config

        $html = "";
        $html .= "<div class='btn-group' " . ($block == "1" ? "style='width:100%;'" : "") . ">\n";
        if ($split == "1") { // split for dropdown menu.

            // Split
            // Input ID is shared betweeen 2 - input_id-1 and -2

            // First Element
            if (is_array($input_value) && (!empty($input_value))) {
                if ($anchor == 1) {
                    $html .= "<a id='$input_id-1' class='btn $class $btn_block dropdown-toggle' data-toggle='dropdown' href='#'>$icon $title <span class='caret'></span></a>\n";
                } else {
                    $html .= "<button id='$input_id-1' name='$input_name' class='btn $class $btn_block  dropdown-toggle' data-toggle='dropdown'>$icon $title <span class='caret'></span></button>\n";
                }
                $html .="<ul class='dropdown-menu' />\n";
                foreach($input_value as $arr=>$v) {
                    $html .= "<li><a href='$arr'>$v</a></li>\n";
                }
                $html .="</ul>\n";
                if ($anchor == 1) {
                    $html .= "</a>\n";
                } else {
                    $html .= "</button>\n";
                }
            } else {
                // run default link button
                if ($anchor == 1) {
                    $html .= "<a id='$input_id-1' class='btn $class $btn_block'  href='$input_value'>$icon $title</a>\n";
                } else {
                    $html .= "<button id='$input_id-1' type='button' name='$input_name' class='btn $class $btn_block '>$icon $title</button>\n";
                }
            }
            // Second Element
            if (is_array($split_link) && (!empty($split_link))) {

                if ($anchor == 1) {
                    $html .= "<a id='$input_id-2' class='btn $split_class dropdown-toggle' data-toggle='dropdown' href='#'>$split_icon $split_title <span style='margin-left:5px' class='caret'></span></a>\n";
                } else {
                    $html .= "<button id='$input_id-2' name='$split_name' class='btn $split_class dropdown-toggle' data-toggle='dropdown' href='#'>$split_icon $split_title <span style='margin-left:5px' class='caret'></span></button>\n";
                }
                $html .="<ul class='dropdown-menu' />\n";
                foreach($split_link as $arr=>$v) {
                    $html .= "<li><a href='$arr'>$v</a></li>\n";
                }
                $html .="</ul>\n";
            } else {
                // run default link button
                if ($anchor == 1) {
                    $html .= "<a id='$input_id-2' class='btn  $split_class' href='$split_link'>$split_icon $split_title</a>\n";
                } else {
                    $html .= "<button id='$input_id-2' type='button' name='$split_name' class='btn $split_class'>$split_icon $split_title</button>\n";
                }
            }
        } else {
            // Non Split
            if (is_array($input_value) && (!empty($input_value))) {
                if ($anchor == 1) {
                    $html .= "<a id='$input_id' class='btn btn-sm $class $btn_block  dropdown-toggle' data-toggle='dropdown' href='#'>$title<span style='margin-left:5px' class='caret'></span></a>\n";
                    $html .= ($icon) ? "<span class='btn btn-icon btn-sm'>$icon</span>" : "";
                } else {
                    $html .= "<button id='$input_id' type='button' class='btn btn-sm $class $btn_block  dropdown-toggle' data-toggle='dropdown' href='#'>$title<span style='margin-left:5px' class='caret'></span></button>\n";
                    $html .= ($icon) ? "<span class='btn btn-icon btn-sm'>$icon</span>" : "";
                }
                $html .="<ul class='dropdown-menu' />\n";
                foreach($input_value as $arr=>$v) {
                    $html .= "<li><a href='$arr'>$v</a></li>\n";
                }
                $html .="</ul>\n";

            } else {
                // run default link button
                if ($anchor == 1) {
                    $html .= "<a id='$input_id' class='btn btn-sm $class $btn_block ' href='$input_value'>$title</a>\n";
                    $html .= ($icon) ? "<span class='btn btn-icon btn-sm'>$icon</span>" : "";
                } else {
                    if ($icon_stack == 1) {
                        $html .= ($icon) ? "<span class='btn btn-icon btn-sm'>$icon</span><br>" : "";
                        $html .= "<button id='$input_id' value='$input_value' type='button' name='$input_name' class='btn btn-sm $class $btn_block '>$title</button>\n";
                    } else {
                        $html .= "<button id='$input_id' value='$input_value' type='button' name='$input_name' class='btn btn-sm $class $btn_block '>$title</button>\n";
                        $html .= ($icon) ? "<span class='btn btn-icon btn-sm'>$icon</span>" : "";
                    }
                }
            }
        }
        $html .= "</div>\n";
        return $html;
    }
    */



    function form_button($title, $input_name, $input_id, $input_value, $array = false)
    {
        $title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

        $html = "";
        if (!is_array($array)) {
            $class = "btn-default";
            $icon = "";
            $type = '';
            $icon_stack = 0;
            $block = 0;
            $btn_block = '';
            $deactivate = '';
        } else {
            $class = array_key_exists("class",$array) ? stripinput($array['class']) : "btn-default";
            $icon = array_key_exists("icon",$array) ? $array['icon'] : "";
            $deactivate = array_key_exists("deactivate",$array) && ($array['deactivate'] == 1) ? 1 : 0;
            $icon_stack = (array_key_exists("icon_stack", $array) && isnum($array['icon_stack']) && ($array['icon_stack'] == 1)) ? 1 : 0;
            $type = (array_key_exists("type",$array) && ($array['type'])) ? $array['type'] : '';
            $block = (array_key_exists("block", $array) && ($array['block'] == 1)) ? 1 : 0;
            $btn_block = ($block == 1) ? "btn-block" : "";
        }

        if ($type == 'link') {
            $html .= "<a id='".$input_id."' class='".($deactivate ? 'disabled' : '')." btn $class' href='".$input_name."' data-value='".$input_value."' ".($deactivate ? "disabled='disabled'" : '')." >".($icon ? "<i class='$icon'></i>" : '')." ".$title."</a>";
        } elseif ($type =='button') {
            $html .= "<button id='".$input_id."' class='".($deactivate ? 'disabled' : '')." btn $class ' name='".$input_name."' value='".$input_value."' type='button' ".($deactivate ? "disabled='disabled'" : '')." >".($icon ? "<i class='$icon'></i>" : '')." ".$title."</button>";
        } else {
            $html .= "<button id='".$input_id."' class='".($deactivate ? 'disabled' : '')." btn $class ' name='".$input_name."' value='".$input_value."' type='submit' ".($deactivate ? "disabled='disabled'" : '')." >".($icon ? "<i class='$icon'></i>" : '')." ".$title."</button>";
        }

        //$html .= ($token == '1') ? generate_token($input_id) : '';
        return $html;
    }


    function form_btngroup($title, $input_name, $input_id, $options, $input_value, $array=false) {

        $title = (isset($title) && (!empty($title))) ? stripinput($title) : "";
        $title2 = ucfirst(strtolower(str_replace("_", " ", $input_name)));
        $input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
        $input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
        $input_value = (isset($input_value) && (!empty($input_value))) ? stripinput($input_value) : "";

        if (!is_array($array)) {
            $class = 'small';
            $justified = "";
            $well = "";
            $wellclass = "";

            $helper_text = "";
            $slider = 0;
            $required = 0;
            $safemode = 0;
            $inline = '';

        } else {

            $class = (array_key_exists("class", $array)) ? $array['class'] : "small";
            $justified = (array_key_exists("justified", $array)) ? "btn-group-justified" : "";
            $well = (array_key_exists('well', $array)) ? "style='margin-top:-10px;'" : "";
            $helper_text = (array_key_exists("helper",$array)) ? $array['helper'] : "";
            $required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
            $safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
            $inline = (array_key_exists("rowstart", $array)) ? 1 : 0;

        }
        $html  = "";

        $html .= (!$inline) ? "<div class='field'/>\n" : '';
        $html .= "<label><h3>$title</h3></label>\n";

        $html .= "<div class='ui buttons $justified' id='".$input_id."'>";

        $x = 1;
        $active = '';
        foreach($options as $arr=>$v) {
            if (($input_value == $arr)) { $active = "active"; } else { $active = ''; }
            $html .= "<span data-value='$arr' class='ui button $class " . ((count($options) == $x ? 'last-child' : '')) . " $active'/>";
            $html .= "$v";
            $html .= "</span>";
            $x++;
        }

        $html .= "<input readonly type='hidden' id='".$input_id."-text' value='$input_value'>\n";
        $html .= "</div>\n";
        add_to_jquery("
        $('#".$input_id." span').bind('click', function(e){
            $('#".$input_id." span').removeClass('active');
            $(this).toggleClass('active');
            value = $(this).data('value');
            $('#".$input_id."-text').val(value);
        });
        ");

        $html .= "<input type='hidden' name='def[$input_name]' value='[type=text],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]' readonly>";

        $html .= (!$inline) ? "</div/>\n" : '';



        return $html;
    }




?>