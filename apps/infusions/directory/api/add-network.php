<?php

use PHPFusion\OutputHandler;

defined('IN_FUSION') || exit;

/**
 * Adds or remove network from input field
 */
function add_network() {
    
    require_once INCLUDES.'ajax_include.php';
    
    if (post('method') == 'add') {
        
        $social_network = post(['social_network']);
        $social_url = post(['social_url']);
        
        if (empty($_SESSION['social_network'])) {
            $_SESSION['social_network'][0] = "";
            $_SESSION['social_url'][0] = "";
        } else {
            array_push($_SESSION['social_network'], '');
            array_push($_SESSION['social_url'], '');
        }
        
        // saves the value into session
        if (!empty($social_network)) {
            foreach ($social_network as $index => $value) {
                $_SESSION['social_network'][$index] = $value;
                $_SESSION['social_url'][$index] = $social_url[$index];
            }
        }
        
        
    } else if (post('method') == 'rm') {
        
        $row = post('row', FILTER_VALIDATE_INT);
        // need to find which row is being deleted.
        unset($_SESSION['social_network'][$row]);
        unset($_SESSION['social_url'][$row]);
    }
    
    for ($i = 0; $i < count($_SESSION['social_network']); $i++) {
        $value_1 = $_SESSION['social_network'][$i] ?? '';
        $value_2 = $_SESSION['social_url'][$i] ?? '';
        
        echo '<div data-row="'.$i.'" class="social-network">'
            .form_select('social_network[]', '', $value_1, [
                'input_id'    => 'socialy_'.$i,
                'allowclear'  => TRUE,
                'placeholder' => 'Select Network', 'width' => '100%', 'inner_width' => '100%',
                'options'     => social_networks()
            ])
            .form_text('social_url[]', '', $value_2, [
                'placeholder' => 'Enter URL...',
                'input_id'    => 'socialx_'.$i
            ])
            ."<a href='#socnet".$i."' data-crows='$i' data-action='network_rm'  class='trash-social btn btn-default'><i class='far fa-trash'></i></a>"
            .'</div>';
    }
    echo '<script>'.OutputHandler::$jqueryTags.'</script>';
}

/*
 * @uses add_network
 */
fusion_add_hook('fusion_filters', 'add_network');

