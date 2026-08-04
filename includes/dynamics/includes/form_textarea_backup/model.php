<?php
function openeditortab($tab_title, $link_active_arrkey, $id, $link = FALSE, $class = FALSE, $getname = "section") {

    $link_mode = ! empty($link) ? $link : 0;
    $html = "<div class='nav-wrapper $class'>\n";
    $html .= "<ul class='nav' " . ( $id ? "id='" . $id . "'" : "" ) . " >";
    if ( ! empty($tab_title['title']) ) {
        foreach ( $tab_title['title'] as $arr => $v ) {
            $v_title = str_replace("-", " ", $v);
            $tab_id = $tab_title['id'][ $arr ];
            $icon = ( isset($tab_title['icon'][ $arr ]) ) ? $tab_title['icon'][ $arr ] : "";
            $link_url = $link ? clean_request($getname . '=' . $tab_id, [ $getname ], FALSE) : '#';
            if ( $link_mode ) {
                $html .= ( $link_active_arrkey == $tab_id ) ? "<li class='active'>" : "<li>";
            }
            else {
                $html .= ( $link_active_arrkey == "" . $tab_id ) ? "<li class='active'>" : "<li>";
            }
            $html .= "<a class='btn btn-default btn-sm m-l-5 pointer' " . ( ! $link_mode ? "id='tab-" . $tab_id . "' data-toggle='tab' data-target='#" . $tab_id . "'" : "href='$link_url'" ) . ">" . ( $icon ? "<i class='" . $icon . "'></i>" : '' ) . " " . $v_title . " </a>";
            $html .= "</li>";
        }
    }
    $html .= "</ul>";

    return dynamics_render_component_template('form_textarea_backup', $html);
}
