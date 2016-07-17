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

        echo opentab($tab, $_GET['ref'], "mood_admin", TRUE, "m-t-10", "ref");

        switch ($_GET['ref']) {
            case "mood_form" :
                $this->displayMoodForm();
                break;
            case "mood_list":
                $this->displayMoodList();
                break;
        }
        echo closetab();

    }

    public $data = array(
        'mood_id' => 0,
        'mood_name' => '',
        'mood_description' => '',
        'mood_icon' => '',
        'mood_notify' => '-101',
        'mood_access' => '-101',
        'mood_status' => 1,
    );

    protected function post_Mood() {

        if (isset($_POST['save_mood'])) {
            $this->data = array(
              "mood_id" => form_sanitizer($_POST['mood_id'], 0, 'mood_id'),
              "mood_name" => form_sanitizer($_POST['mood_name'], '', 'mood_name', TRUE),
              "mood_description" => form_sanitizer($_POST['mood_description'], '', 'mood_description', TRUE),
              "mood_icon" => form_sanitizer($_POST['mood_icon'], '', 'mood_icon'),
              "mood_status" => form_sanitizer($_POST['mood_status'], '', 'mood_status'),
              "mood_notify" => form_sanitizer($_POST['mood_notify'], '', 'mood_notify'),
              "mood_access" => form_sanitizer($_POST['mood_access'], '', 'mood_access'),
            );

            if (\defender::safe()) {
                if (!empty($this->data['mood_id'])) {
                    dbquery_insert(DB_FORUM_MOODS, $this->data, 'update');
                    addNotice('success', "Forum Mood updated");
                } else {
                    dbquery_insert(DB_FORUM_MOODS, $this->data, 'save');
                    addNotice('success', "Forum Mood created");
                }
                redirect( clean_request('', array('mood_id', 'ref'), FALSE));
            }

        }

    }

    private function displayMoodForm() {

        $this->post_Mood();

        $quantum = new QuantumFields();

        $groups = fusion_get_groups();
        unset($groups[0]);

        if (!empty($_GET['action'])) {

            $validMoodID = isset($_GET['mood_id']) && isnum($_GET['mood_id'])
            && !empty(dbcount('(mood_id)', DB_FORUM_MOODS, "mood_id=".$_GET['mood_id'])) ? TRUE : FALSE;

            switch($_GET['action']) {
                case 'edit':

                    if ($validMoodID) {
                        $query = "SELECT * FROM ".DB_FORUM_MOODS." WHERE mood_id='".intval($_GET['mood_id'])."'";
                        $result = dbquery($query);
                        if (dbrows($result)>0) {
                            $this->data = dbarray($result);
                        } else {
                            redirect( clean_request('', array('ref', 'mood_id'), FALSE ));
                        }
                    } else {
                        redirect( clean_request('', array('ref', 'mood_id'), FALSE ));
                    }
                    break;
                case 'delete':
                    if ($validMoodID) {
                        addNotice('success', 'Forum Mood deleted');
                        dbquery("DELETE FROM ".DB_FORUM_MOODS." WHERE mood_id='".intval($_GET['mood_id'])."'");
                    } else {
                        redirect( clean_request('', array('ref', 'mood_id'), FALSE ));
                    }
                    break;
                default:
                    redirect( clean_request('', array('ref', 'mood_id'), FALSE ));
            }
        }


        echo openform("mood_form", "POST", FUSION_REQUEST, array('class' => 'm-t-20 m-b-20')).
            form_hidden('mood_id', '', $this->data['mood_id']).
            $quantum->quantum_multilocale_fields('mood_name', 'Mood Name Locale', $this->data['mood_name'], array(
                'required' => TRUE, 'inline' => TRUE, 'width' => '350px', 'placeholder' => 'Like'
            )).
            $quantum->quantum_multilocale_fields('mood_description', 'Mood Description Locale',
                                                 $this->data['mood_description'],
                                                 array(
                                                     'required' => TRUE, 'inline' => TRUE, 'width' => '350px',
                                                     'placeholder' => 'Liked',
                                                     'ext_tip' => 'Single word abbreviation to describe the mood (e.g. Liked)',
                                                 )).
            form_text('mood_icon', 'Mood Button Icon', $this->data['mood_icon'],
                      array('inline' => TRUE, 'width' => '350px')).
            form_checkbox('mood_status', 'Mood Button Status', $this->data['mood_status'],
                          array(
                              'options' => array(
                                  'Hide and do not use this mood',
                                  'This mood is active'
                              ),
                              'inline' => TRUE,
                              'type' => 'radio'
                          )).
            form_checkbox('mood_notify', 'Mood Notifications Level', $this->data['mood_notify'],
                          array(
                              'options' => $groups,
                              'inline' => TRUE,
                              'type' => 'radio'
                          )).
            form_checkbox('mood_access', 'Mood Button Visibility', $this->data['mood_access'], array(
                'options' => $groups,
                'inline' => TRUE,
                'type' => 'radio'
            )).
            form_button('save_mood', 'Save Mood', 'save_mood', array('class' => 'btn-primary m-r-10')).
            form_button('cancel_mood', 'Cancel', 'cancel').
            closeform();
    }

    private function displayMoodList() {

        $mood_max_count = dbcount("(mood_id)", DB_FORUM_MOODS, "");

        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $mood_max_count ? intval($_GET['rowstart']) : 0;
        $mood_query = "SELECT * FROM ".DB_FORUM_MOODS." ORDER BY mood_id ASC LIMIT 0, 16";
        $mood_result = dbquery( $mood_query);
        $rows = dbrows($mood_result);
        if ($rows > 0) :
        ?>
        <table class="table table-responsive table-striped table-hover m-t-20 m-b-20">
            <thead>
            <tr>
                <td class="col-xs-2">Mood Name</td>
                <td class="col-xs-2">Mood Description</td>
                <td>Mood Button Preview</td>
                <td>Mood Button Notification Level</td>
                <td>Mood Button Visibility</td>
                <td>Actions</td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <?php while ($data = dbarray( $mood_result) ) : ?>
                    <td><?php echo QuantumFields::parse_label($data['mood_name']) ?></td>
                    <td><?php echo sprintf("User %s the current post", QuantumFields::parse_label( $data['mood_description'])) ?></td>
                    <td><button class="btn btn-xs btn-default disabled">
                            <?php if (!empty($data['mood_icon'])) : ?>
                                <i class="<?php echo $data['mood_icon'] ?>"></i>
                            <?php endif; ?>
                            <?php echo QuantumFields::parse_label($data['mood_name']) ?>
                        </button>
                    </td>
                    <td><?php echo getgroupname($data['mood_notify']) ?></td>
                    <td><?php echo getgroupname($data['mood_access']) ?></td>
                    <td>
                        <a href="<?php echo clean_request("ref=mood_form&action=edit&mood_id=".$data['mood_id'], array("ref", "action", "mood_id"), FALSE) ?>">Edit</a> -
                        <a href="<?php echo clean_request("ref=mood_form&action=delete&mood_id=".$data['mood_id'], array("ref", "action", "mood_id"), FALSE) ?>">Delete</a>
                    </td>
                <?php endwhile; ?>
            </tr>
            </tbody>
        </table>

        <?php if ($mood_max_count > $rows) echo makepagenav($_GET['rowstart'], $rows, $mood_max_count, 3); ?>

        <?php else : ?>
            <div class="well text-center">There are no forum mood available</div>
        <?php endif;
    }

}
