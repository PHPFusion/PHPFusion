<?php
namespace ThemePack\Nebula\Templates;

class Comment {

    private static $instance = NULL;

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function display_comment($c_data, $c_info, $index = 0) {

        $locale = fusion_get_locale();

        $comments_html = "";

        if (!empty($c_data)) {

            $c_makepagenav = ($c_info['c_makepagenav'] !== FALSE) ? "<div class=\"text-center m-b-5\">".$c_info['c_makepagenav']."</div>\n" : "";

            $comments_html .= "<ul class='comments clearfix'>\n";

            $comments_html .= self::display_all_comments($c_data);

            $comments_html .= $c_makepagenav;

            if ($c_info['admin_link'] !== FALSE) {
                $comments_html .= "<div style='float:right' class='comment_admin'>".$c_info['admin_link']."</div>\n";
            }
            $comments_html .= "</ul>\n";

        } else {
            $comments_html .= "<div class='well text-center'>\n";
            $comments_html .= $locale['c101']."\n";
            $comments_html .= "</div>\n";
        }
        ?>

        <!---comments form--->
        <div class="comments-panel">
            <!---comments header-->
            <div class="comments-header">
                <?php echo $c_info['comments_count'] ?>
            </div>
            <!---//comments header-->
            <div class="comments overflow-hide">
                <?php echo $comments_html ?>
            </div>
        </div>
        <!---//comments form--->
        <?php
    }

    private static function display_all_comments($c_data, $index = 0, &$comments_html = FALSE) {
        // Make base comment
        $comments_html = '';

        foreach ($c_data[$index] as $comments_id => $data) {

            $comments_html .= "<!---comment-".$data['comment_id']."---><li class='m-b-15'>\n";
            $action = "";
            if ($data['edit_dell'] !== FALSE) {
                $action = "<div class='display-block text-right'>";
                $action .= "<div class='comment-actions'>".$data['edit_dell']."</div>\n";
                $action .= "</div>";
            }

            $comments_html .= sprintf(($index == 0 ? self::get_baseTemplate() : self::get_replyTemplate()),
                display_avatar($data['user'], '60px', '', FALSE, 'img-circle m-r-20'),
                "<h4 class='comment-name'>".$data['comment_name']."</h4>",
                "<a href='".FUSION_REQUEST."#c".$data['comment_id']."' id='c".$data['comment_id']."' name='c".$data['comment_id']."'>".$data['comment_datestamp']."</a>",
                "<a href='".$data['reply_link']."' class='btn btn-default btn-bordered btn-md'>".fusion_get_locale('c112',
                                                                                                                   LOCALE.LOCALESET."comments.php")."</a>",
                $data['comment_message'],
                $action

            );

            if (!empty($data['reply_form'])) {
                // Decorate this tomorrow
                $comments_html .= $data['reply_form'];
            }

            // replies is here
            if (isset($c_data[$comments_id])) {
                $comments_html .= "<ul class='sub-comments'>\n";
                $comments_html .= self::display_all_comments($c_data, $comments_id);
                $comments_html .= "</ul>\n";
            }

            $comments_html .= "</li><!---//comment-".$comments_id."--->";
        }

        return $comments_html;
    }

    private static function get_baseTemplate() {
        ob_start();
        ?>
        <div class="comment-item well">
            <div class="comment-head">
                <div class="comment-head-user">
                    <div class="comment-user-avatar pull-left">%s</div>
                    %s %s
                </div>
                <div class="comment-head-action">%s</div>
            </div>
            <div class="comment-text">%s</div>
            %s
        </div>
        <?php
        $base_comment = ob_get_contents();
        ob_end_clean();

        return (string)$base_comment;
    }

    private static function get_replyTemplate() {
        ob_start();
        ?>
        <div class="sub-comment-item well">
            <div class="comment-head">
                <div class="comment-head-user">
                    <div class="comment-user-avatar pull-left">%s</div>
                    %s %s
                </div>
                <div class="comment-head-action">%s</div>
            </div>
            <div class="comment-text">%s</div>
            %s
        </div>
        <?php
        $base_comment = ob_get_contents();
        ob_end_clean();

        return (string)$base_comment;
    }
}
