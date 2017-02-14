<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/templates/forum_thread.php
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
/**
 * Thread Page HTML
 */
if (!function_exists('render_thread')) {
    function render_thread($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."forum/templates/css/forum.css'>");
        $locale = fusion_get_locale();
        $buttons = !empty($info['buttons']) ? $info['buttons'] : array();
        $data = !empty($info['thread']) ? $info['thread'] : array();
        $pdata = !empty($info['post_items']) ? $info['post_items'] : array();
        $can_poll = ($info['permissions']['can_create_poll'] && $info['permissions']['can_post'] ? TRUE : FALSE);
        $can_post = ($info['permissions']['can_post'] ? TRUE : FALSE);
        $can_bounty = ($info['permissions']['can_start_bounty'] ? TRUE : FALSE);
        $selector['oldest'] = $locale['forum_0180'];
        $selector['latest'] = $locale['forum_0181'];
        $filter_dropdown = '';
        if (!empty($info['post-filters'])) {
            $filter_dropdown .= "<ul class='dropdown-menu'>\n";
            foreach ($info['post-filters'] as $i => $filters) {
                $filter_dropdown .= "<li><a class='text-smaller' href='".$filters['value']."'>".$filters['locale']."</a></li>\n";
            }
            $filter_dropdown .= "</ul>\n";
        }
        $post_items = '';
        if (!empty($pdata)) {
            foreach ($pdata as $post_id => $post_data) {
                $post_items .= "<!--forum_thread_prepost_".$post_data['post_id']."-->\n";
                $post_items .= render_post_item($post_data);
                if ($post_id == $info['post_firstpost']) {
                    if ($info['permissions']['can_post']) {
                        $post_items .= "<div class='text-right m-t-10 m-b-20'>\n";
                        $post_items .= "<h4 class='display-inline-block strong'>".$info['thread_posts']."</h4>\n";
                        $post_items .= "<a class='m-l-20 btn btn-success btn-md vatop ".(empty($buttons['reply']) ? 'disabled' : '')."' href='".$buttons['reply']['link']."'>".$buttons['reply']['title']."</a>\n";
                        $post_items .= "</div>\n";
                    }
                    if ($info['thread_bounty']) {
                        $post_items .= $info['thread_bounty'];
                    }
                }
            }
        }
        $participated_users = '';
        if (!empty($info['thread_users'])) {
            $participated_users .= "<div class='list-group-item m-b-20'>\n";
            $participated_users .= "<span class='m-r-10'><strong>".$locale['forum_0581']."</strong></span>";
            $i = 1;
            $max = count($info['thread_users']);
            foreach ($info['thread_users'] as $user_id => $users) {
                $participated_users .= $users;
                $participated_users .= $max == $i ? " " : ", ";
                $i++;
            }
            $participated_users .= "</div>\n";
        }

        /*
         * Template Design - Modify $html to mod template outlook
         * Note: It's 1/2 slower, but we should have access to OpCache, but this method is the best for Template work
         * since you can edit the HTML output without worrying about the PHP Codes. However, when forum gets updated in the future,
         * you can easily check the difference in replacement {%tags%} and add the design into your custom function work.
         */
        $template = "
        <section class='thread'>
            {%breadcrumbs%}
            <h2>{%sticky_icon%}{%locked_icon%}{%thread_subject%}</h2>
            <div class='clearfix m-b-20'>
                <div class='last-updated'>{%time_updated%}<i class='fa fa-calendar fa-fw'></i> </div>
                {%thread_tags%}
            </div>
            {%poll_form%}
            <div class='clearfix'>
                <div class='clearfix'>
                    <div class='pull-right'>{%bounty_button%} {%poll_button%} {%new_thread_button%}</div>
                    <div class='pull-left'>
                        <div class='dropdown display-inline-block m-r-10'>
                            <a class='btn btn-sm btn-default dropdown-toggle' data-toggle='dropdown'><strong>".$locale['forum_0183']."</strong> {%filter_word%}<span class='caret'></span></a>
                            {%filter_dropdown%}
                        </div>
                        <div class='btn-group'>
                        {%notify_button%}{%print_button%}
                        </div>
                    </div>
                </div>
                {%pagenav%}
            </div>
            <!--pre_forum_thread-->
            {%post_items%}            
            {%mod_form%}            
            <div class='clearfix m-t-20'>
                <div class='pull-left m-t-10'>
                {%new_thread_button%}{%reply_button%}
                </div>
                {%pagenav%}
            </div>
            {%quick_reply_form%}
            <div class='list-group-item m-t-20 m-b-20'>
                {%info_access%}
                {%info_bounty%}
                {%info_post%}
                {%info_reply%}
                {%info_create_poll%}
                {%info_edit_poll%}
                {%info_vote_poll%}
                {%info_upload%}
                {%info_download%}
                {%info_rate%}
            </div>
            {%info_moderators%}
            {%info_users%}
        </section>
        ";

        /*
         * Replacement
         */
        echo strtr($template,
            [
                '{%breadcrumbs%}'       => render_breadcrumbs(),
                '{%pagenav%}'           => (isset($info['page_nav']) ? "<div class='pull-right m-t-10 text-lighter clearfix'>\n".$info['page_nav']."</div>\n" : ''),
                '{%sticky_icon%}'       => ($data['thread_sticky'] == TRUE ? "<i title='".$locale['forum_0103']."' class='".get_forumIcons("sticky")."'></i>" : ''),
                '{%locked_icon%}'       => ($data['thread_locked'] == TRUE ? "<i title='".$locale['forum_0102']."' class='".get_forumIcons("lock")."'></i>" : ''),
                '{%thread_subject%}'    => $data['thread_subject'],
                '{%time_updated%}'      => $locale['forum_0363'].timer($data['thread_lastpost']),
                '{%thread_tags%}'       => (!empty($info['thread_tags_display']) ? "<div class='clearfix'><i class='fa fa-tags text-lighter fa-fw'></i> ".$info['thread_tags_display']."</div>" : ''),
                '{%poll_form%}'         => (!empty($info['poll_form']) ? "<div class='well'>".$info['poll_form']."</div>" : ''),
                '{%poll_button%}'       => ($can_poll ? "<a class='btn btn-success btn-sm m-r-10 ".(!empty($info['thread']['thread_poll']) ? 'disabled' : '')."' title='".$buttons['poll']['title']."' href='".$buttons['poll']['link']."'>".$buttons['poll']['title']." <i class='fa fa-pie-chart'></i> </a>" : ''),
                '{%bounty_button%}'     => ($can_bounty ? "<a class='btn btn-primary btn-sm ".(!empty($info['thread']['thread_bounty']) ? 'disabled' : '')."' title='".$buttons['bounty']['title']."' href='".$buttons['bounty']['link']."'>".$buttons['bounty']['title']." <i class='fa fa-dot-circle-o'></i></a>\n" : ''),
                '{%new_thread_button%}' => ($can_post ? "<a class='btn btn-primary btn-sm ".(empty($buttons['newthread']) ? 'disabled' : '')." ' href='".$buttons['newthread']['link']."'>".$buttons['newthread']['title']."</a>" : ''),
                '{%reply_button%}'      => ($can_post ? "<a class='btn btn-primary btn-sm m-l-10 ".(empty($buttons['reply']) ? 'disabled' : '')."' href='".$buttons['reply']['link']."'>".$buttons['reply']['title']."</a>" : ''),
                '{%filter_word%}'       => (isset($_GET['section']) && in_array($_GET['section'], array_flip($selector)) ? $selector[$_GET['section']] : $locale['forum_0180']),
                '{%filter_dropdown%}'   => $filter_dropdown,
                '{%notify_button%}'     => (!empty($buttons['notify']) ? "<a class='btn btn-default btn-sm' title='".$buttons['notify']['title']."' href='".$buttons['notify']['link']."'>".$buttons['notify']['title']." <i class='fa fa-eye'></i></a>\n" : ''),
                '{%print_button%}'      => "<a class='btn btn-default btn-sm' title='".$buttons['print']['title']."' href='".$buttons['print']['link']."'>".$buttons['print']['title']." <i class='fa fa-print'></i></a>",
                '{%mod_form%}'          => (iMOD ? "<div class='list-group-item'>".$info['mod_form']."</div>\n" : ''),
                '{%post_items%}'        => $post_items,
                '{%quick_reply_form%}'  => (!empty($info['quick_reply_form']) ? "<hr/>\n".$info['quick_reply_form'] : ''),
                '{%info_access%}'       => (sprintf($locale['forum_perm_access'], $info['permissions']['can_access'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n"),
                '{%info_post%}'         => (sprintf($locale['forum_perm_post'], $info['permissions']['can_post'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n"),
                '{%info_reply%}'        => (sprintf($locale['forum_perm_reply'], $info['permissions']['can_reply'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n"),
                '{%info_edit_poll%}'    => ($data['thread_poll'] ? (sprintf($locale['forum_perm_edit_poll'], $info['permissions']['can_edit_poll'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n") : ''),
                '{%info_vote_poll%}'    => ($data['thread_poll'] ? (sprintf($locale['forum_perm_vote_poll'], $info['permissions']['can_vote_poll'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>"))."<br/>\n" : ''),
                '{%info_create_poll%}'  => (!$data['thread_poll'] ? (sprintf($locale['forum_perm_create_poll'], $info['permissions']['can_create_poll'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n") : ''),
                '{%info_upload%}'       => (sprintf($locale['forum_perm_upload'], $info['permissions']['can_upload_attach'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n"),
                '{%info_download%}'     => (sprintf($locale['forum_perm_download'], $info['permissions']['can_download_attach'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n"),
                '{%info_rate%}'         => ($data['forum_type'] == 4 ? (sprintf($locale['forum_perm_rate'], $info['permissions']['can_rate'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n") : ''),
                '{%info_bounty%}'       => ($data['forum_type'] == 4 ? (sprintf($locale['forum_perm_bounty'], $info['permissions']['can_start_bounty'] ? "<strong class='text-success'>".$locale['can']."</strong>" : "<strong class='text-danger'>".$locale['cannot']."</strong>")."<br/>\n") : ''),
                '{%info_moderators%}'   => ($info['forum_moderators'] ? "<div class='list-group-item m-b-20'>".$locale['forum_0185']." ".$info['forum_moderators']."</div>" : ''),
                '{%info_users%}'        => $participated_users,
            ]
        );
    }
}

/* Post Item */
if (!function_exists('render_post_item')) {

    function render_post_item($data) {

        $aidlink = fusion_get_aidlink();
        $forum_settings = \PHPFusion\Forums\ForumServer::get_forum_settings();
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();
        ob_start();

        $template = "
        {%post_html_comment%}
        <div id='{%item_marker_id%}' class='clearfix post_items'>
            <div class='clearfix'>
                <div class='forum_avatar text-center'>{%user_avatar%}{%user_avatar_rank%}</div>
                <!--forum_thread_user_name-->
                <div class='m-b-10 post_info'>
                    {%user_online_status%} <span class='text-smaller'><span class='forum_poster'>{%user_profile_link%}</span>{%user_rank%}{%post_date%}</span>
                    <div class='pull-right m-l-10'>
                        <div class='clearfix'>
                            <div class='display-inline-block m-l-10 pull-right'>{%checkbox_input%}</div>
                            <div class='btn-group'>
                                {%quote_button%}{%reply_button%}{%edit_button%}
                                <a class='dropdown-toggle btn btn-xs btn-default' data-toggle='dropdown'><i class='fa fa-ellipsis-v'></i></a>
                                <ul class='dropdown-menu'>
                                    {%li_user_ip%}
                                    {%li_user_post_count%}
                                    {%li_message%}
                                    {%li_web%}
                                    {%li_print%}
                                    {%li_quote%}
                                    {%li_edit%}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='overflow-hide'>
                {%vote_form%}
                <div class='overflow-hide'>\n
                    <div class='post_message'>{%post_message%}</div>
                    {%user_signature%}
                    {%post_attach%}
                </div>
                <!--sub_forum_post_message-->
                <div class='post_info m-t-20'>\n
                    {%post_edit_reason%}
                    {%post_reply_message%}
                    {%post_mood_message%}
                </div>
                <!--//sub_forum_post_message-->
		    </div>
		    {%post_mood%} {%bounty_button%}
        </div>
        ";

        $li_admin = '';
        if ($data['user_id'] != 1) {
            if (iSUPERADMIN || (iADMIN && checkrights('M'))) {
                $li_admin .= "<li class='divider'></li>\n";
                $li_admin .= "<p class='text-center'><a href='".ADMIN."members.php".$aidlink."&amp;step=edit&amp;user_id=".$data['user_id']."'>".$locale['edit']."</a> &middot; ";
                $li_admin .= "<a href='".ADMIN."members.php".$aidlink."&amp;user_id=".$data['user_id']."&amp;action=1'>".$locale['ban']."</a> &middot; ";
                $li_admin .= "<a href='".ADMIN."members.php".$aidlink."&amp;step=delete&amp;status=0&amp;user_id=".$data['user_id']."'>".$locale['delete']."</a></p>\n";
            }
        }

        return strtr($template,
            [
                '{%post_html_comment%}'  => "<!--forum_thread_prepost_".$data['post_id']."-->",
                '{%post_date%}'          => $data['post_shortdate'],
                '{%item_marker_id%}'     => $data['marker']['id'],
                '{%user_avatar%}'        => $data['user_avatar_image'],
                '{%user_avatar_rank%}'   => ($forum_settings['forum_rank_style'] == '1' ? "<div class='m-t-10'>".$data['user_rank']."</div>\n" : ''),
                '{%user_rank%}'          => ($forum_settings['forum_rank_style'] == '0' ? "<span class='forum_rank'>\n".$data['user_rank']."</span>\n" : ''),
                '{%user_profile_link%}'  => $data['user_profile_link'],
                '{%user_online_status%}' => "<span style='height:5px; width:10px; border-radius:50%; color:#5CB85C'><i class='fa ".($data['user_online'] ? "fa-circle" : "fa-circle-thin")."'></i></span>",
                '{%user_signature%}'     => ($data['user_sig'] ? "<div class='forum_sig text-smaller'>".$data['user_sig']."</div>\n" : ""),
                '{%checkbox_input%}'     => (iMOD ? $data['post_checkbox'] : ''),
                '{%post_message%}'       => $data['post_message'],
                '{%bounty_button%}'      => ($data['post_bounty'] ? "<!---bounty--><a class='btn btn-success pull-right' href='".$data['post_bounty']['link']."'>".$data['post_bounty']['title']."</a><!--//post_bounty-->" : ''),
                '{%post_mood%}'          => (!empty($data['post_mood']) ? "<!--forum_mood--><div class='pull-right m-t-10 m-b-10'>".$data['post_mood']."</div><!--//forum_mood-->" : ""),
                '{%post_edit_reason%}'   => $data['post_edit_reason'],
                '{%post_reply_message%}' => $data['post_reply_message'],
                '{%post_mood_message%}'  => $data['post_mood_message'],
                '{%post_attach%}'        => ($data['post_attachments'] ? "<div class='forum_attachments'>".$data['post_attachments']."</div>" : ""),
                '{%quote_button%}'       => (isset($data['post_quote']) && !empty($data['post_quote']) ? "<a class='btn btn-default btn-xs quote-link' href='".$data['post_quote']['link']."' title='".$data['post_quote']['title']."'>".$data['post_quote']['title']."</a>\n" : ''),
                '{%reply_button%}'       => (isset($data['post_reply']) && !empty($data['post_reply']) ? "<a class='btn btn-default btn-xs reply-link' href='".$data['post_reply']['link']."' title='".$data['post_reply']['title']."'>".$data['post_reply']['title']."</a>\n" : ''),
                '{%edit_button%}'        => (isset($data['post_edit']) && !empty($data['post_edit']) ? "<a class='btn btn-default btn-xs edit-link' href='".$data['post_edit']['link']."' title='".$data['post_edit']['title']."'>".$data['post_edit']['title']."</a>\n" : ""),
                '{%li_user_ip%}'         => ($data['user_ip'] ? "<li><i class='fa fa-user fa-fw'></i> IP : ".$data['user_ip']."</li>" : ""),
                '{%li_user_post_count%}' => "<li><i class='fa fa-commenting-o fa-fw'></i> ".$data['user_post_count']."</li>",
                '{%li_message%}'         => ($data['user_message']['link'] !== "" ? "<li><a href='".$data['user_message']['link']."' title='".$data['user_message']['title']."'>".$data['user_message']['title']."</a></li>\n" : ""),
                '{%li_web%}'             => ($data['user_web']['link'] ? "<li>".(fusion_get_settings('index_url_userweb') ? "" : "<!--noindex-->")." <a href='".$data['user_web']['link']."' title='".$data['user_web']['title']."' ".(fusion_get_settings('index_url_userweb') ? "" : "rel='nofollow'").">".$data['user_web']['title']."</a>".(fusion_get_settings('index_url_userweb') ? "" : "<!--/noindex-->")."</li>\n" : ""),
                '{%li_print%}'           => "<li><a href='".$data['print']['link']."' title='".$data['print']['title']."'>".$data['print']['title']."</a></li>\n",
                '{%li_quote%}'           => (isset($data['post_quote']) && !empty($data['post_quote']) ? "<li><a href='".$data['post_quote']['link']."' title='".$data['post_quote']['title']."'>".$data['post_quote']['title']."</a></li>\n" : ''),
                '{%li_edit%}'            => (isset($data['post_edit']) && !empty($data['post_edit']) ? "<li><a href='".$data['post_edit']['link']."' title='".$data['post_edit']['title']."'>".$locale['forum_0507']."</a></li>\n" : ''),
                '{%li_admin%}'           => $li_admin,
                '{%vote_form%}'          => ($data['post_votebox'] ? "<div class='pull-left m-r-15'>".$data['post_votebox'].$data['post_answer_check']."</div>" : ''),
            ]
        );

    }
}