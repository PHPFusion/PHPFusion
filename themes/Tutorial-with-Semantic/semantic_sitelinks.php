<?php

/**
 * Custom Show Sub links
 * Copied codes from theme_functions_include.php
 * then compared to Semantic UI "html markup and css class"
 * and make modifications accordingly (manual merging)
 */

function showsublinks($sep = "", $class = "", array $options = array(), $id = 0) {

    $pageInfo   = pathinfo($_SERVER['SCRIPT_NAME']);
    $start_page = $pageInfo['dirname'] !== "/" ? ltrim($pageInfo['dirname'], "/")."/" : "";
    $site_path  = ltrim(fusion_get_settings("site_path"), "/");
    $start_page = str_replace($site_path, "", $start_page);
    $start_page .= $pageInfo['basename'];

    static $data = array();

    $res = &$res;

    if (empty($data)) {
        $data = dbquery_tree_full(DB_SITE_LINKS, "link_id", "link_cat", "WHERE link_position >= 2".(multilang_table("SL") ? " AND link_language='".LANGUAGE."'" : "")." AND ".groupaccess('link_visibility')." ORDER BY link_cat, link_order");
    }

    if ($id == 0) {
        $res = '
        <div class="ui fixed inverted menu">
        <div class="ui container">
        <!---Menu Header Start--->
        <a class="header item" href="'.BASEDIR.'index.php">
            <img src="'.IMAGES.'php-fusion-icon.png" alt="'.fusion_get_settings("sitename").'"/>
            <span class="display-inline-block m-l-10">'.fusion_get_settings("sitename").'</span>
        </a>
        <!---Menu Header End--->
        <!---Menu Item Start--->
        ';
    } else {
        $res .= '<div class="ui simple dropdown item">';
        $res .= '<div class="menu">';
    }
    if (!empty($data)) {
        $i = 0;
        foreach ($data[$id] as $link_id => $link_data) {

            $li_class = $class;

            // Attempt to calculate a relative link

            $secondary_active = FALSE;

            if ($start_page !== $link_data['link_url']) {
                $link_instance = \PHPFusion\BreadCrumbs::getInstance();
                $link_instance->showHome(FALSE);
                $reference = $link_instance->toArray();
                if (!empty($reference)) {
                    foreach ($reference as $refData) {
                        if (!empty($refData['link']) && $link_data['link_url'] !== "index.php") {
                            if (stristr($refData['link'], str_replace("index.php", "", $link_data['link_url']))) {
                                $secondary_active = TRUE;
                            }
                            break;
                        }
                    }
                }
            }

            if ($link_data['link_name'] != "---" && $link_data['link_name'] != "===") {

                $link_target = ($link_data['link_window'] == "1" ? " target='_blank'" : "");

                if ($i == 0 && $id > 0) {
                    $li_class .= ($li_class ? " " : "")."first-link";
                }

                if ($start_page == $link_data['link_url']
                    || $secondary_active == TRUE
                    || $start_page == fusion_get_settings("opening_page") && $i == 0 && $id === 0
                ) {
                    $li_class .= ($li_class ? " " : "")."current-link active";
                }

                if (preg_match("!^(ht|f)tp(s)?://!i", $link_data['link_url'])) {
                    $itemlink = $link_data['link_url'];
                } else {
                    $itemlink = BASEDIR.$link_data['link_url'];
                }

                $res .= $sep.'

                <a class="item'.($li_class ? " ".$li_class : "").'" href="'.$itemlink.'" '.$link_target.'>'.$link_data['link_name'].'</a>';

                if (isset($data[$link_id])) {
                    $res .= showsublinks($sep, $class, $options, $link_data['link_id']);
                }

            } elseif ($link_data['link_cat'] > 0) {
                echo "<div class='divider'></div>";
            }
            $i++;
        }
    }
    if ($id == 0) {
        $res .= "<!---Menu Item End--->\n";
        $res .= "</div>\n";
        $res .= "</div>\n</div>\n";
    } else {
        $res .= "</div>\n";
    }
    return $res;
}

/* The HTML markup that was originally in theme.php
 * To be deleted when function HTML is finished.
 *
 *
 * <div class="ui fixed inverted menu">
        <div class="ui container">
            <a class='header item' href='<?php echo BASEDIR."index.php" ?>'>
                <img src="<?php echo IMAGES."php-fusion-icon.png" ?>"
                     alt="<?php echo fusion_get_settings("sitename") ?>"/>
                <span class="display-inline-block m-l-10"><?php echo fusion_get_settings("sitename") ?></span>
            </a>
            <a href="#" class="item">Home</a>

            <div class="ui simple dropdown item"> -- first tiered dropdown need .ui .simple .dropdown .item ..?
                Dropdown <i class="dropdown icon"></i>

                <div class="menu">
                    <a class="item" href="#">Link Item</a>
                    <a class="item" href="#">Link Item</a>

                    <div class="divider"></div> -- done
                    <div class="header">Header Item</div> -- header item dont have
                    <div class="item">
                        <i class="dropdown icon"></i>
                        Sub Menu
                        <div class="menu"> -- seems like if have dropdown, just add this in
                            <a class="item" href="#">Link Item</a>
                            <a class="item" href="#">Link Item</a>
                        </div>
                    </div>
                    <a class="item" href="#">Link Item</a>
                </div>
            </div>
        </div>
    </div>
 */