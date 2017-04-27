<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: classes/admin/mood.php
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

class ForumAdminMood extends ForumAdminInterface {

    /**
     * Forum mood data
     * @var array
     */
    private $data = array(
        'mood_id' => 0,
        'mood_name' => '',
        'mood_description' => '',
        'mood_icon' => '',
        'mood_notify' => '-101',
        'mood_access' => '-101',
        'mood_status' => 1,
    );


    public function viewMoodAdmin() {
        $aidlink = fusion_get_aidlink();
        pageAccess('F');
        \PHPFusion\BreadCrumbs::getInstance()->addBreadCrumb([
                           'link' => INFUSIONS.'forum/admin/forums.php'.$aidlink.'&section=fmd',
                           'title' => self::$locale['forum_admin_004']
                       ]);

        echo "<div class='well'>".self::$locale['forum_090']."</div>\n";

        $tab['title'][] = self::$locale['forum_093'];
        $tab['id'][] = "mood_list";
        $tab['icon'][] = "";

        $tab['title'][] = isset($_GET['mood_id']) && isnum($_GET['mood_id']) ? self::$locale['forum_092'] : self::$locale['forum_091'];
        $tab['id'][] = "mood_form";
        $tab['icon'][] = "";

        $_GET['ref'] = isset($_GET['ref']) && in_array($_GET['ref'], $tab['id']) ? $_GET['ref'] : "mood_list";

        echo opentab($tab, $_GET['ref'], "mood_admin", TRUE, "nav-tabs m-t-10", "ref", ['mood_id', 'action']);
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

    /**
     * Displays forum mood form
     */
    private function displayMoodForm() {

        $locale = fusion_get_locale('', FORUM_ADMIN_LOCALE);

        fusion_confirm_exit();

        $this->post_Mood();

        $quantum = new QuantumFields();

        $groups = fusion_get_groups();
        unset($groups[0]);

        if (!empty($_GET['action'])) {

            $validMoodID = isset($_GET['mood_id']) && isnum($_GET['mood_id'])
            && dbcount('(mood_id)', DB_FORUM_MOODS, "mood_id=".$_GET['mood_id']) ? TRUE : FALSE;

            switch ($_GET['action']) {
                case 'edit':

                    if ($validMoodID) {
                        $query = "SELECT * FROM ".DB_FORUM_MOODS." WHERE mood_id='".intval($_GET['mood_id'])."'";
                        $result = dbquery($query);
                        if (dbrows($result) > 0) {
                            $this->data = dbarray($result);
                        } else {
                            redirect(clean_request('', array('ref', 'mood_id'), FALSE));
                        }
                    } else {
                        redirect(clean_request('', array('ref', 'mood_id'), FALSE));
                    }
                    break;
                case 'delet':
                    if ($validMoodID) {
                        addNotice('success', $locale['forum_notice_14']);
                        dbquery("DELETE FROM ".DB_FORUM_MOODS." WHERE mood_id='".intval($_GET['mood_id'])."'");
                    } else {
                        redirect(clean_request('', array('ref', 'mood_id'), FALSE));
                    }
                    break;
                default:
                    redirect(clean_request('', array('ref', 'mood_id'), FALSE));
            }
        }


        echo openform("mood_form", "POST", FUSION_REQUEST, array('class' => 'm-t-20 m-b-20')).
            form_hidden('mood_id', '', $this->data['mood_id']).
            $quantum->quantum_multilocale_fields('mood_name', $locale['forum_094'], $this->data['mood_name'], array(
                'required' => TRUE, 'inline' => TRUE, 'placeholder' => $locale['forum_096']
            )).
            $quantum->quantum_multilocale_fields('mood_description', $locale['forum_095'],
                                                 $this->data['mood_description'],
                                                 array(
                                                     'required' => TRUE, 'inline' => TRUE,
                                                     'placeholder' => $locale['forum_097'],
                                                     'ext_tip' => $locale['forum_098']
                                                 )).
            form_text('mood_icon', $locale['forum_099'], $this->data['mood_icon'],
                      array('inline' => TRUE, 'width' => '350px')).
            form_checkbox('mood_status', $locale['forum_100'], $this->data['mood_status'],
                          array(
                              'options' => array(
                                  $locale['forum_101'],
                                  $locale['forum_102']
                              ),
                              'inline' => TRUE,
                              'type' => 'radio'
                          )).
            form_checkbox('mood_notify', $locale['forum_103'], $this->data['mood_notify'],
                          array(
                              'options' => $groups,
                              'inline' => TRUE,
                              'type' => 'radio'
                          )).
            form_checkbox('mood_access', $locale['forum_104'], $this->data['mood_access'], array(
                'options' => $groups,
                'inline' => TRUE,
                'type' => 'radio'
            ));
            echo form_button('save_mood', !empty($this->data['mood_id']) ? $locale['forum_106'] : $locale['forum_105'], $locale['save_changes'], array('class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o'));
            echo form_button('cancel_mood', $locale['cancel'], $locale['cancel'], array('icon' => 'fa fa-times'));
            echo closeform();
    }

    /**
     * Post execution of forum mood
     */
    protected function post_Mood() {
        $locale = fusion_get_locale('', FORUM_ADMIN_LOCALE);
        if (isset($_POST['cancel_mood'])) {
            redirect(clean_request('', array('mood_id', 'ref'), FALSE));
        }

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
                    addNotice('success', $locale['forum_notice_16']);
                } else {
                    dbquery_insert(DB_FORUM_MOODS, $this->data, 'save');
                    addNotice('success', $locale['forum_notice_15']);
                }
                redirect(clean_request('', array('mood_id', 'ref'), FALSE));
            }

        }

    }

    /**
     * Displays forum mood listing
     */
    private function displayMoodList() {

        $locale = fusion_get_locale('', FORUM_ADMIN_LOCALE);

        $mood_max_count = dbcount("(mood_id)", DB_FORUM_MOODS, "");

        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $mood_max_count ? intval($_GET['rowstart']) : 0;

        $mood_query = "SELECT fm.*, count(post_id) 'mood_count' FROM ".DB_FORUM_MOODS." fm
        LEFT JOIN ".DB_POST_NOTIFY." pn ON pn.notify_mood_id = fm.mood_id
        GROUP BY mood_id ORDER BY mood_id ASC LIMIT 0, 16";

        $mood_result = dbquery($mood_query);

        $rows = dbrows($mood_result);

        if ($rows > 0) :

            ?>
            <table class="table table-responsive table-striped table-hover m-t-20 m-b-20">
                <thead>
                <tr>
                    <td class="col-xs-2"><?php echo $locale['forum_107'] ?></td>
                    <td class="col-xs-2"><?php echo $locale['forum_108'] ?></td>
                    <td><?php echo $locale['forum_109'] ?></td>
                    <td><?php echo $locale['forum_115'] ?></td>
                    <td><?php echo $locale['forum_110'] ?></td>
                    <td><?php echo $locale['forum_111'] ?></td>
                    <td><?php echo $locale['forum_112'] ?></td>
                </tr>
                </thead>
                <tbody>

                <?php while ($data = dbarray($mood_result)) :
                    $edit_link = clean_request("ref=mood_form&action=edit&mood_id=".$data['mood_id'],
                                               array("ref", "action", "mood_id"), FALSE);
                    $delete_link = clean_request("ref=mood_form&action=delet&mood_id=".$data['mood_id'],
                                                 array("ref", "action", "mood_id"), FALSE);
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo $edit_link ?>">
                                <?php echo QuantumFields::parse_label($data['mood_name']) ?>
                            </a>
                        </td>
                        <td><?php echo sprintf($locale['forum_113'],
                                               ucfirst(fusion_get_userdata("user_name")),
                                               QuantumFields::parse_label($data['mood_description'])) ?>
                        </td>
                        <td>
                            <?php if (!empty($data['mood_icon'])) : ?>
                                <i class="<?php echo $data['mood_icon'] ?>"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo format_word($data['mood_count'], $locale['fmt_post']) ?></td>
                        <td><?php echo getgroupname($data['mood_notify']) ?></td>
                        <td><?php echo getgroupname($data['mood_access']) ?></td>
                        <td>
                            <a href="<?php echo $edit_link ?>"><?php echo $locale['edit'] ?></a> -
                            <a href="<?php echo $delete_link ?>"><?php echo $locale['delete'] ?></a>
                        </td>
                    </tr>
                <?php endwhile; ?>

                </tbody>
            </table>

            <?php if ($mood_max_count > 16) {
            echo makepagenav($_GET['rowstart'], $rows, $mood_max_count, 3);
        } ?>

        <?php else : ?>
            <div class="well text-center"><?php echo $locale['forum_114'] ?></div>
        <?php endif;
    }

}
