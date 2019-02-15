<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: AdminForm.inc
| Author: Frederick MC Chan
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';

require_once THEMES.'templates/admin_header.php';

/**
 * Babylon Administration FORM SDK
 * Class Demo_Form
 * Demo of Dependency Injection Approach(DI).
 *
 * NOTE: THIS IS A WORKING CONCEPT BUT WORK IN PROGRESS BEFORE WE CAN CALL IT FULLY DEVELOPED.
 *
 *
 * We wills standardize and update all our Infusions with this.
 *
 *
 */
class Demo_Form implements \PHPFusion\AdminFormSDK {

    public function properties() {
        // WIP
    }

    /**
     * This is your SAVE method.
     * This function is flexible so you can do what you need to regarding the save action.
     * @param $data
     *
     * @return int
     */
    public function save($data) {
        dbquery_insert(DB_NEWS, $data, 'update');
        return (int)dblastid();
    }
    /**
     * This is your UPDATE method.
     * This function is flexible so you can do what you need to regarding the update action.
     * @param $data
     *
     * @return int
     */
    public function update($data) {
        return (int)dbquery_insert(DB_NEWS, $data, 'save');
    }

    /**
     * This is your initial data.
     * @return array
     */
    public function data() {

        if (isset($_GET['edit']) && isnum($_GET['edit'])) {
            $result = dbquery("SELECT * FROM ".DB_NEWS." WHERE news_id=:nid", [':nid'=>intval($_GET['edit'])]);
            if (dbrows($result)) {
                return dbarray($result);
            }
        }
        return array(
            'news_id' => 0,
            'news_subject' => '',
            'news_description' => '',
            'news_price' => '',
            'news_startdate' => TIME,
            'news_enddate' => 0,
            'news_visibility' => USER_LEVEL_PUBLIC,
            'news_status'=>0,
        );
    }

    /**
     * Field Constructor
     * Dynamics Output but in Array Control Format.
     * @return array|mixed
     */
    public function fields() {
        return array(
            // the types are already specifically put. all you need to fill is the name, label and options.
            'id' => array(
                'name' => 'news_id',
            ),
            'title' => array(
                'name' => 'news_subject',
                'label' => 'News Subject',
                'options' => [
                    'placeholder'=>'Enter a news title',
                    'inner_class'=>'input-lg',
                    'required'=>TRUE
                ]
            ),
            'startdate'=> array(
                'name' =>'news_startdate'
            ),
            'enddate' => array(
                'name' => 'news_enddate',
            ),
            'visibility' => array(
                'name' => 'news_visibility'
            ),
            'status' => array(
                'name'=>'news_status',
            ),
            'description' => array(
                'name'=>'news_body',
                'options' => array(
                    'type' => 'bbcode',
                    'autosize' => TRUE,
                )
            )
        );
    }

    /**
     * This is where the custom fields are, you can do tabs, accordions and all the funky stuff.
     * @param $data -- This can be either 2 - WHEN POST'ed, it is sanitized data. When NOT, it is non-sanitized data. When Edit,... well, you get the idea. So we don't need to run if/else.
     *
     * @return string
     * @throws Exception
     */
    public function custom($data) {
        return form_text('news_price', 'News Price (USD)', (!empty($data['news_price']) ? number_format($data['news_price'],2) : ''), [
            'placeholder'=>'0.00',
            'required'=>TRUE,
            'inline'=>TRUE,
            'inner_width'=>'300px',
            'type'=>'number',
            'number_step'=>'0.1'
        ]);
        // you can keep adding all fields, but remember, to increment the property of the data() function of this class with your init data.


    }



}

// Ok now you have your class,...
add_breadcrumb([
    'title' => 'News',
    'link'  => 'whatever.php',
]);

opentable('News Form Demo');

$news_form = new Demo_Form(); // This is your demo form ? Create it.

new \PHPFusion\AdminForm( $news_form ); // feed the whole class into it... LOL. Everything runs automatic from there.

/**
 * The plan with AdminForm class will be building widgets, and widgets, and more widget stuff.
 * - Advantage: Possibly cut down file size on all infusions.
 * - Advantage: FAST TO CODE.
 * - Advantage: Optimize Performance across the whole board of Infusions with standardized framework.
 *
 * DOING MORE WITH FEWER CODES.
 */

closetable();

require_once THEMES.'templates/footer.php';