<?php

use PHPFusion\OutputHandler;

require_once __DIR__."/../../maincore.php";
require_once INCLUDES."theme_functions_include.php";
require_once TEMPLATES."render_functions.php";

function add_links($data) {

    if (!empty($data["link_position"]) && !empty($data["link_name"])) {
        $data["link_status"] = 0;
        $data["link_id"] = 0;
        $data["link_type"] = "link";

        $link_id = (int)dbquery_insert(DB_SITE_LINKS, $data, "save");

        echo '<li id="menuItem_'.$link_id.'">';
        echo '<div style="max-width:400px;">';
        echo opencollapse($link_id);
        echo opencollapsebody($data["link_name"], $link_id."m", $link_id);
        echo openform("_form", "post", FORM_REQUEST, array("form_id" => "_form_".$link_id));
        echo form_hidden("_type", "", "");
        echo form_hidden("_id", "", $link_id);
        echo form_hidden("_language", "", LANGUAGE);
        echo form_hidden("_position", "", $data["link_position"]);
        echo form_text("_url", "URL", $data["link_url"], array("input_id" => "_url_".$link_id, "required" => FALSE));
        echo form_text("_name", "Name", $data["link_name"], array("input_id" => "_name_".$link_id, "required" => TRUE));
        echo form_text("_title", "Title Attribute", '', array("input_id" => "_title_".$link_id, "required" => FALSE));
        echo form_text("_icon", "Icon Class", '', array("input_id" => "_icon_".$link_id));
        echo form_checkbox("_window", "Open link in a new tab", '', array("input_id" => "_url_".$link_id, "type" => "checkbox", "reverse_label" => TRUE));
        echo form_textarea("_description", "Description", '', array("input_id" => "_description_".$link_id, "ext_tip" => "The description will be displayed in the menu if the current theme supports it."));
        echo form_select("_visibility", "Visibility", "", array("input_id" => "_visibility_".$link_id, "options" => fusion_get_groups()));
        echo form_checkbox("_status", "Status", "", array("input_id" => "_status_".$link_id, "type" => "radio", "options" => get_status_opts()));
        echo '<a href="#" class="remove_link text-danger" data-id="'.$link_id.'" data-action="remove">Remove</a>';
        echo form_button("save_link", "Save Link", "save_link", array("input_id" => "_save_".$link_id, "class" => "btn-primary"));
        echo closeform();
        echo closecollapsebody();
        echo closecollapse();
        echo '</div>';
        echo "</li>";
        echo "<script>".jsminify(OutputHandler::$jqueryTags)."</script>";

    } else {
        echo '<li id="error"><div class="well"><strong>Erorr in creating a link</strong></li>';
    }
}
