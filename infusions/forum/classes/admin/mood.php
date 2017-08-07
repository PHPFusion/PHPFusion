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
        'mood_id'          => 0,
        'mood_name'        => '',
        'mood_description' => '',
        'mood_icon'        => '',
        'mood_notify'      => USER_LEVEL_MEMBER,
        'mood_access'      => USER_LEVEL_MEMBER,
        'mood_status'      => 1,
    );

    public function viewMoodAdmin() {
        pageAccess('F');

        echo "<div class='well m-t-15'>".self::$locale['forum_090']."</div>\n";
        $mood_pages = array("mood_list", "mood_form");

        if (isset($_GET['ref']) && $_GET['ref'] == "back") {
            redirect(clean_request("section=fmd", array("ref", "section", 'mood_id', 'rowstart'), FALSE));
        }

        $_GET['ref'] = isset($_GET['ref']) && in_array($_GET['ref'], $mood_pages) ? $_GET['ref'] : $mood_pages[0];

        if ($_GET['ref'] != $mood_pages[0]) {
            $tab['title'][] = self::$locale['back'];
            $tab['id'][] = "back";
            $tab['icon'][] = "fa fa-fw fa-arrow-left";
        } else {

        $tab['title'][] = self::$locale['forum_093'];
        $tab['id'][] = "mood_list";
        $tab['icon'][] = "fa fa-fw fa-eye";
        }
        $tab['title'][] = isset($_GET['mood_id']) && isnum($_GET['mood_id']) ? self::$locale['forum_092'] : self::$locale['forum_091'];
        $tab['id'][] = "mood_form";
        $tab['icon'][] = isset($_GET['mood_id']) && isnum($_GET['mood_id']) ? "fa fa-fw fa fa-pencil" : "fa fa-fw fa fa-plus";

        $_GET['ref'] = isset($_GET['ref']) && in_array($_GET['ref'], $tab['id']) ? $_GET['ref'] : "mood_list";

        echo opentab($tab, $_GET['ref'], "mood_admin", TRUE, "nav-tabs m-t-10", "ref", ['mood_id', 'action']);

        switch ($_GET['ref']) {
            case "mood_form" :
                $this->displayMoodForm();
                break;
            default:
                $this->displayMoodList();
        }
        echo closetab();
    }
    /**
     * Displays forum mood form
     */
    private function displayMoodForm() {

        if (isset($_POST['cancel_mood'])) {
            redirect(clean_request('', array('mood_id', 'ref'), FALSE));
        }

        $this->post_Mood();

        $groups = fusion_get_groups();
        unset($groups[0]);

        $form_action = clean_request("section=fmd&ref=mood_form", array("mood_id", "ref"), FALSE);

        if (isset($_GET['mood_id']) && isnum($_GET['mood_id'])) {

            $result = dbquery("SELECT * FROM ".DB_FORUM_MOODS." WHERE mood_id='".intval($_GET['mood_id'])."'");

            if (dbrows($result) > 0) {

                $this->data = dbarray($result);

                $form_action = clean_request("section=fmd&ref=mood_form&mood_id=".$_GET['mood_id'], array("mood_id", "ref"), FALSE);
            }
        }

        echo openform("mood_form", "POST", $form_action).
            form_hidden('mood_id', '', $this->data['mood_id']).

            \PHPFusion\QuantumFields::quantum_multilocale_fields('mood_name', self::$locale['forum_094'], $this->data['mood_name'],
                      ['required' => TRUE, 'inline' => TRUE, 'placeholder' => self::$locale['forum_096']]).

            \PHPFusion\QuantumFields::quantum_multilocale_fields('mood_description', self::$locale['forum_095'], $this->data['mood_description'],
                      ['required' => TRUE, 'inline' => TRUE, 'placeholder' => self::$locale['forum_097'], 'ext_tip' => self::$locale['forum_098']]).

            form_text('mood_icon', self::$locale['forum_099'], $this->data['mood_icon'],
                      ['inline' => TRUE, 'width' => '350px', 'placeholder' => 'fa fa-thumbs-up']).

            form_checkbox('mood_status', self::$locale['forum_100'], $this->data['mood_status'],
                      ['options' => [
                           self::$locale['forum_101'],
                           self::$locale['forum_102']
                           ],
                       'inline' => TRUE,
                       'type' => 'radio'
                      ]).

            form_checkbox('mood_notify', self::$locale['forum_103'], $this->data['mood_notify'],
                      ['options' => $groups, 'inline' => TRUE, 'type' => 'radio']).

            form_checkbox('mood_access', self::$locale['forum_104'], $this->data['mood_access'],
                      ['options' => $groups, 'inline' => TRUE, 'type' => 'radio']);

            echo form_button('save_mood', !empty($this->data['mood_id']) ? self::$locale['forum_106'] : self::$locale['forum_105'], self::$locale['save_changes'], array('class' => 'btn-success m-r-10', 'icon' => 'fa fa-hdd-o'));
            echo form_button('cancel_mood', self::$locale['cancel'], self::$locale['cancel'], array('icon' => 'fa fa-times'));
            echo closeform();
    }
    /**
     * Post execution of forum mood
     */
    protected function post_Mood() {

        if (isset($_POST['save_mood'])) {
            $this->data = [
                'mood_id'          => form_sanitizer($_POST['mood_id'], 0, 'mood_id'),
                'mood_name'        => form_sanitizer($_POST['mood_name'], '', 'mood_name', TRUE),
                'mood_description' => form_sanitizer($_POST['mood_description'], '', 'mood_description', TRUE),
                'mood_icon'        => form_sanitizer($_POST['mood_icon'], '', 'mood_icon'),
                'mood_status'      => form_sanitizer($_POST['mood_status'], '', 'mood_status'),
                'mood_notify'      => form_sanitizer($_POST['mood_notify'], '', 'mood_notify'),
                'mood_access'      => form_sanitizer($_POST['mood_access'], '', 'mood_access'),
            ];

            if (\defender::safe()) {
                if (!empty($this->data['mood_id'])) {
                    dbquery_insert(DB_FORUM_MOODS, $this->data, 'update');
                    addNotice('success', self::$locale['forum_notice_16']);
                } else {
                    dbquery_insert(DB_FORUM_MOODS, $this->data, 'save');
                    addNotice('success', self::$locale['forum_notice_15']);
                }
                redirect(clean_request('', array('mood_id', 'ref'), FALSE));
            }
        }

        if (isset($_GET['delete']) && isnum($_GET['delete'])) {
             addNotice('success', self::$locale['forum_notice_14']);
             dbquery("DELETE FROM ".DB_FORUM_MOODS." WHERE mood_id='".intval($_GET['delete'])."'");
             redirect(clean_request("section=fmd", array("delete", "ref"), FALSE));
        }
    }
    /**
     * Displays forum mood listing
     */
    private function displayMoodList() {

        $mood_max_count = dbcount("(mood_id)", DB_FORUM_MOODS, "");

        $_GET['rowstart'] = isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $mood_max_count ? intval($_GET['rowstart']) : 0;

        $mood_query = "SELECT fm.*, count(pn.post_id) AS 'mood_count'
            FROM ".DB_FORUM_MOODS." fm
            LEFT JOIN ".DB_POST_NOTIFY." pn ON pn.notify_mood_id=fm.mood_id
            GROUP BY mood_id
            ORDER BY mood_id ASC
            LIMIT ".$_GET['rowstart'].", 16";

        $mood_result = dbquery($mood_query);

        $rows = dbrows($mood_result);

        if ($rows > 0) :

            ?>
            <div class="table-responsive"><table class="table table-striped table-hover m-t-20 m-b-20">
                <thead>
                <tr>
                    <td class="col-xs-2"><?php echo self::$locale['forum_107'] ?></td>
                    <td class="col-xs-2"><?php echo self::$locale['forum_108'] ?></td>
                    <td><?php echo self::$locale['forum_109'] ?></td>
                    <td><?php echo self::$locale['forum_115'] ?></td>
                    <td><?php echo self::$locale['forum_110'] ?></td>
                    <td><?php echo self::$locale['forum_111'] ?></td>
                    <td><?php echo self::$locale['forum_112'] ?></td>
                </tr>
                </thead>
                <tbody>

                <?php while ($data = dbarray($mood_result)) :
                    $edit_link = clean_request("section=fmd&ref=mood_form&mood_id=".$data['mood_id'], ["ref", "mood_id"], FALSE);
                    $delete_link = clean_request("section=fmd&ref=mood_form&delete=".$data['mood_id'], ["ref", "mood_id"], FALSE);
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo $edit_link ?>">
                                <?php echo QuantumFields::parse_label($data['mood_name']) ?>
                            </a>
                        </td>
                        <td><?php echo sprintf(self::$locale['forum_113'],
                                               ucfirst(fusion_get_userdata("user_name")),
                                               QuantumFields::parse_label($data['mood_description'])) ?>
                        </td>
                        <td>
                            <?php if (!empty($data['mood_icon'])) : ?>
                                <i class="<?php echo $data['mood_icon'] ?>"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo format_word($data['mood_count'], self::$locale['fmt_post']) ?></td>
                        <td><?php echo getgroupname($data['mood_notify']) ?></td>
                        <td><?php echo getgroupname($data['mood_access']) ?></td>
                        <td>
                            <a href="<?php echo $edit_link ?>"><?php echo self::$locale['edit'] ?></a> -
                            <a href="<?php echo $delete_link ?>"><?php echo self::$locale['delete'] ?></a>
                        </td>
                    </tr>
                <?php endwhile; ?>

                </tbody>
            </table></div>

            <?php if ($mood_max_count > 16) {
            echo makepagenav($_GET['rowstart'], $rows, $mood_max_count, 3, FUSION_SELF.fusion_get_aidlink()."&section=fmd&");
        } ?>

        <?php else : ?>
            <div class="well text-center m-t-10"><?php echo self::$locale['forum_114'] ?></div>
        <?php endif;
    }
}
