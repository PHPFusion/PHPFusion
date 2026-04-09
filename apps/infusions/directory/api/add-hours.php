<?php

use PHPFusion\OutputHandler;

defined('IN_FUSION') || exit;

/**
 * Adds or remove network from input field
 */
function add_hours() {
    
    require_once INCLUDES.'ajax_include.php';
    
    $day = post('day', FILTER_VALIDATE_INT);
    
    if (post('method') == 'add') {
        
        $opening_hours = post(['opening_hours', $day]);
        $closing_hours = post(['closing_hours', $day]);
        if (!empty($opening_hours)) {
            foreach($opening_hours as $index => $value) {
                $_SESSION['opening_hours'][$day] = $opening_hours;
            }
        }
        if (!empty($closing_hours)) {
            foreach ($closing_hours as $index => $value) {
                $_SESSION['closing_hours'][$day] = $closing_hours;
            }
        }
        
        if (empty($_SESSION['opening_hours'][$day])) {
            $_SESSION['opening_hours'][$day][0] = "";
            $_SESSION['closing_hours'][$day][0] = "";
        } else {
            
            array_push($_SESSION['opening_hours'][$day][], '');
            array_push($_SESSION['closing_hours'][$day][], '');
        }
    
    } else if (post('method') == 'rm') {
        /* removes row */
        $day = post('day', FILTER_VALIDATE_INT);
        $row = post('row', FILTER_VALIDATE_INT);
        unset($_SESSION['opening_hours'][$day][$row]);
        unset($_SESSION['closing_hours'][$day][$row]);
    }
    $last_index = 0;
    $last_end_time = 0;
    $html = '';
    if (!empty($_SESSION['opening_hours'][$day])) {
        
        $working_time = working_time();
        
        // rows
 
        for ($i = 0; $i < count($_SESSION['opening_hours'][$day]); $i++) {
    
            if ($last_end_time == '23:45') {
                break;
            }
            
            $input_value_1 = $_SESSION['opening_hours'][$day][$i] ?? '';
            $input_value_2 = $_SESSION['closing_hours'][$day][$i] ?? ''; // 00:30
            
            // Sets the hour multiple options
            if ($last_end_time) {
                $keys = array_keys($working_time);
                foreach ($keys as $index => $val) {
                    if ($last_end_time === $val) {
                        $last_index = $index;
                        break;
                    }
                }
                $starting_hours_opts = array_slice($working_time, $last_index, 99, TRUE);
                $ending_hour_opts = array_slice($working_time, $last_index + 1, 99, TRUE);
                
            } else if (!$i) {
                // this is first
                $starting_hours_opts = $working_time;
                $ending_hour_opts = $working_time;
            }
            
            if ($input_value_2) {
                $last_end_time = $input_value_2;
            }
            
            $html .= '<div data-row="'.$i.'" data-day="'.$day.'" class="work-hours">'
                .form_select('opening_hours['.$day.'][]', '', $input_value_1, [
                    'input_id'    => 'opening_hours_'.$day.'_'.$i,
                    'placeholder' => 'Opens From',
                    'options'     => $starting_hours_opts ?? $working_time
                ])
                .form_select('closing_hours['.$day.'][]', '', $input_value_2, [
                    'input_id'    => 'closing_hours_'.$day.'_'.$i,
                    'placeholder' => 'Closes From',
                    'options'     => $ending_hour_opts ?? $working_time
                ])
                ."<a href='#' data-crows='$i' data-day='$day' data-action='hours_rm' class='btn btn-default'><i class='far fa-trash'></i></a>"
                .'</div>';
            
        }
    }
    
    if ($last_end_time != '23:45') {
        $html .= "<div class='button-wrapper'>".form_button('add_hours', 'Add hours', $day, [
                'input_id' => 'addhours'.$day,
                'data'     => [
                    'action' => 'add-hours',
                    'crows'  => $day
                ],
                'class'    => 'btn-default btn-md btn-block']).'</div>';
    }
    
    echo $html;
    
    echo '<script>'.OutputHandler::$jqueryTags.'</script>';
}

/*
 * @uses add_network
 */
fusion_add_hook('fusion_filters', 'add_hours');

