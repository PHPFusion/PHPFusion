<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: administration/dashboard/summary/summary.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

function display_news_widget() {
    if (infusion_exists('news')) {
        $uid = (int)fusion_get_userdata('user_id');
        $aid = fusion_get_aidlink();

        if (post('save_news')) {
            $news = [
                'news_subject'   => sanitizer('news_subject', '', 'news_subject'),
                'news_name'      => $uid,
                'news_news'      => sanitizer('news_news', '', 'news_news'),
                'news_draft'     => 1,
                'news_datestamp' => TIME,
            ];
            if (\Defender::safe()) {
                dbquery_insert(DB_NEWS, $news, 'save');
                addNotice('success', 'News draft has been posted');
                redirect(FUSION_REQUEST);
            }
        }

        $html = fusion_get_function("open_sidex", "Quick News Draft");
        $html .= openform('newsfrm', 'post');
        $html .= form_text('news_subject', 'News Subject', '', ['required' => TRUE]);
        $html .= form_textarea('news_news', 'News Snippet', '');
        $html .= form_button('save_news', 'Save News Draft', 'save_news', ['class' => 'btn-primary']);
        $html .= closeform();

        $html .= "<hr/>";
        $html .= '<h5>Your Recent Drafts</h5>';

        $cond = '';
        $param['uid'] = $uid;
        if (multilang_column('N')) {
            $cond = ' news_language=:language AND ';
            $param[':language'] = LANGUAGE;
        }
        $sql = /** @lang sql */
            "SELECT news_id, news_subject, news_news, news_datestamp FROM ".DB_NEWS." WHERE".$cond."news_name=:uid AND news_draft=1 ORDER BY news_datestamp DESC";
        $result = dbquery($sql, $param);

        if (dbrows($result)) {
            $html .= "<ul class='block'>";
            while ($data = dbarray($result)) {
                $edit_link = INFUSIONS.'news/news_admin.php'.$aid.'&amp;action=edit&amp;ref=news_form&amp;news_id='.$data['news_id'];
                $html .= "<li><a href='$edit_link'>".$data['news_subject']."</a> <small>".showdate('newsdate', $data['news_datestamp'])."</small><br/>".strip_tags($data['news_news'])."</li>";
            }
            $html .= "</ul>";
        } else {
            $html .= "You do not have any news draft.";
        }
        $html .= fusion_get_function("close_sidex");

        return $html;
    }

    return NULL;
}