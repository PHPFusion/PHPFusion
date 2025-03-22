<?php

// passing a category tree value results to sitelinks form by position 
function sl_get_category_by_menu_id() {
    require_once INCLUDES . 'ajax_include.php';
    header_content_type('json');
    $pos_id = get('val', FILTER_VALIDATE_INT );
    
    if ($pos_id) {

        $tree = dbquery_tree_full(DB_SITE_LINKS, 'link_id', 'link_cat', filter: 'WHERE link_position="' . $pos_id .'" AND link_language="'.LANGUAGE.'"');

        if (!empty($tree)) {
            
            $select_options = array();
            $options =  get_form_select_opts($tree, array('title_col' => 'link_name'));
            if (!empty($options)) {
                $select_options[0] = fusion_get_locale('parent');
                $select_options += $options;
            }

            echo json_encode(['response' => 200, 'data' => $select_options, 'message'=>'success']);

        } else {

            echo json_encode(['response' => 300, 'data' => array(), 'message' => 'No results found']);
        }
        
    } else {

        echo json_encode(['response'=>301, 'data'=>array(), 'message'=> 'Invalid menu id']);
    }        
}

fusion_add_hook("fusion_admin_hooks", "sl_get_category_by_menu_id");