<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: news.php
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
    if (infusion_exists('news') && checkrights('N')) {
        $uid = (int)fusion_get_userdata('user_id');
        $aid = fusion_get_aidlink();
        // Ajax this part
        if (post('save_news')) {
            $news = [
                'news_subject'   => sanitizer('news_subject', '', 'news_subject'),
                'news_name'      => $uid,
                'news_news'      => sanitizer('news_news', '', 'news_news'),
                'news_draft'     => 1,
                'news_datestamp' => TIME,
            ];
            if (fusion_safe()) {
                dbquery_insert(DB_NEWS, $news, 'save');
                addNotice('success', 'News draft has been posted');
                redirect(FUSION_REQUEST);
            }
        }

        $cond = '';
        $param['uid'] = $uid;
        if (multilang_column('N')) {
            $cond = ' news_language=:language AND ';
            $param[':language'] = LANGUAGE;
        }
        $sql = /** @lang sql */
            "SELECT news_id, news_subject, news_news, news_datestamp FROM ".DB_NEWS." WHERE".$cond."news_name=:uid AND news_draft=1 ORDER BY news_datestamp DESC";
        $result = dbquery($sql, $param);

        $content = [];

        if (dbrows($result)) {
            while ($data = dbarray($result)) {
                $edit_link = INFUSIONS.'news/news_admin.php'.$aid.'&action=edit&ref=news_form&news_id='.$data['news_id'];
                $content[] = [
                    'link'    => $edit_link,
                    'subject' => $data['news_subject'],
                    'date'    => showdate('newsdate', $data['news_datestamp']),
                    'text'    => strip_tags($data['news_news'])
                ];
            }
        }

        $info = [
            'form'       => [
                'openform'     => openform('newsfrm', 'post'),
                'closeform'    => closeform(),
                'news_subject' => form_text('news_subject', 'News Subject', '', ['required' => TRUE]),
                'news_news'    => form_textarea('news_news', 'News Snippet', ''),
                'submit'       => form_button('save_news', 'Save News Draft', 'save_news', ['class' => 'btn-primary'])
            ],
            'content'    => $content,
            'no_content' => 'You do not have any news draft.',
        ];

        return fusion_render(ADMIN.'dashboard/news/', 'news.twig', $info, TRUE);
    }

    return NULL;
}
