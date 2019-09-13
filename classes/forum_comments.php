<?php

function forum_comments() {
    echo "
    <div class='comments-form-panel'>   
        <div class='comments-form' style='display:none;'>
        <a id='{%comment_form_id%}' name='edit_comment'></a>            
            {%comment_name_input%}
            {%comment_subject_input%}
            {%comments_ratings_input%}
            {%comment_message_input%}
            {%comments_captcha_input%}
            {%comment_post%}
        </div>
    </div>
    ";
}

function forum_comments_ui() {
    ?>
    <div class='comments'>
        {%comments_form%}
        <div class="comments-wrapper">
            <div id='{%comment_form_container_id%}' class='comments-header' style="font-size:13px; font-weight:400;border: 0; background:#F5F5F5 none 0 0; color:#555; border-bottom: solid 1px #FFFFFF;padding: 12px 10px;"><i class="fas fa-comment-alt m-r-5"></i>{%comment_count%}</div>
            <div id='{%comment_container_id%}'>
                {%comments_listing%}
            </div>
        </div>
    </div>
    <?php
}

function display_comments_list($info = []) {
    ?>
    <li id='{%comment_list_id%}'>
        <div class="comments-li">
            <div class="m-b-10">
                <?php if (fusion_get_settings('comments_avatar')) : ?>
                    <div class='pull-left text-center m-r-15'>{%user_avatar%}</div>
                <?php endif ?>
                <div class='overflow-hide'>
                    <div class="pull-right"><a href="#{%comment_list_id%}">{%comment_marker%}</a></div>
                    <div class='comment_name display-inline-block m-r-10'>{%user_name%} commented<br/><span class='comment_date'>{%comment_date%}</span></div>
                    <?php if ($info['comment_ratings']) : ?>{%comment_ratings%}<?php endif; ?>
                    <?php if ($info['comment_subject']) : ?><div class='comment_title'><!--comment_subject-->{%comment_subject%}<!--//comment_subject--></div><?php endif; ?>
                </div>
            </div>
            <div class='comment_message'><!--comment_message-->{%comment_message%}<!--//comment_message--></div>
            <div class="comment-actions m-5">
                <?php
                echo !empty($info['reply_link']) ? '{%comment_reply_link%}' : '';
                echo !empty($info['edit_link']) ? ' &middot; {%comment_edit_link%}' : '';
                echo !empty($info['delete_link']) ? ' &middot; {%comment_delete_link%}' : '';
                ?>
            </div>
        </div>

        {%comment_reply_form%}
        <ul class='sub_comments list-style-none'>
            {%comment_sub_comments%}
        </ul>
    </li>
    <?php
}