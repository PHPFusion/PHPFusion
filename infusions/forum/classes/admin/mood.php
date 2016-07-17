<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/admin/post.php
| Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Forums\Admin;

use PHPFusion\QuantumFields;

class ForumMood extends ForumAdminInterface {


    public function viewMoodAdmin() {

        global $aidlink;
        pageAccess('FR');
        add_breadcrumb(array(
                           'link' => INFUSIONS.'forum/admin/forums.php'.$aidlink.'&section=fmd',
                           'title' => self::$locale['forum_admin_004']
                       ));

        echo "<div class='well'>".self::$locale['forum_090']."</div>\n";

        $tab['title'][] = self::$locale['forum_093'];
        $tab['id'][] = "mood_list";
        $tab['icon'][] = "";

        $tab['title'][] = isset($_GET['mood_id']) && isnum($_GET['mood_id']) ? self::$locale['forum_092'] : self::$locale['forum_091'];
        $tab['id'][] = "mood_form";
        $tab['icon'][] = "";

        $_GET['ref'] = isset($_GET['ref']) && in_array($_GET['ref'], $tab['id']) ? $_GET['ref'] : "mood_list";

        echo opentab($tab, $_GET['ref'] ,"mood_admin", TRUE, "m-t-10", "ref");

        switch($_GET['ref']) {
            case "mood_form" :
                $this->displayMoodForm();
                break;
            case "mood_list":


        }
        echo closetab();

    }

    public $data = array(
        'mood_id' => 0,
        'mood_name' => '',
        'mood_description' => '',
        'mood_icon' => '',
        'mood_notify' => '',
        'mood_access' => '',
        'mood_status' => '',
    );

    public function displayMoodForm(){
        $quantum = new QuantumFields();
        echo openform("mood_form", "POST", FUSION_REQUEST, array('class'=>'m-t-20 m-b-20')).
            form_hidden('mood_id', '', $this->data['mood_id']).
            $quantum->quantum_multilocale_fields('mood_name', 'Mood Name Locale', $this->data['mood_name'], array(
                'required'=>TRUE, 'inline'=>TRUE, 'width'=>'350px', 'placeholder'=>'Like')).
            $quantum->quantum_multilocale_fields('mood_description', 'Mood Description Locale', $this->data['mood_description'],
                                                 array('required'=>TRUE, 'inline'=>TRUE, 'width'=>'350px',
                                                       'placeholder' => 'Liked',
                                                       'ext_tip' => 'Single word abbreviation to describe the mood (e.g. Liked)',
                                                 )).
            form_text('mood_icon', 'Mood Button Icon', $this->data['mood_icon'], array('inline'=>TRUE, 'width'=>'350px')).
            form_checkbox('mood_status', 'Mood Button Status', $this->data['mood_status'],
                      array('options' => array(
                          'Hide and do not use this mood',
                          'This mood is active'),
                            'inline' => TRUE,
                            'type'=>'radio'
                      )).
            form_checkbox('mood_notify', 'Mood Notifications Level', $this->data['mood_notify'],
                          array('options' => fusion_get_groups(),
                                'inline' => TRUE,
                                'type'=>'radio'
                          )).
            form_checkbox('mood_access', 'Mood Button Visibility', $this->data['mood_access'], array('options'=>fusion_get_groups(),
                                                                                              'inline'=>TRUE,
                                                                                              'type' => 'radio'
            )).
            form_button('save_mood', 'Save Mood', 'save_mood', array('class'=>'btn-primary m-r-10')).
            form_button('cancel_mood', 'Cancel', 'cancel').
            closeform();
    }


}
