<?php
namespace PHPFusion;

class Comments {

    private $locale = array();
    private $userdata = array();
    private $settings = array();
    private $postLink = "";
    private static $instances = array();

    private $c_arr = array(
        "c_con" => array(),
        "c_info" => array(
            "c_makepagenav" => FALSE,
            "admin_link" => FALSE
        )
    );

    /**
     * Get an instance by key
     *
     * @param string $key
     *
     * @return static
     */
    public static function getInstance($key = 'default') {
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new static();
        }
        return self::$instances[$key];
    }


    public function __construct() {

        $this->settings = fusion_get_settings();

        $this->locale = fusion_get_locale("", LOCALE.LOCALESET."comments.php");

        $this->userdata = fusion_get_userdata();

        $this->postLink = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
        $this->postLink = preg_replace("^(&amp;|\?)c_action=(edit|delete)&amp;comment_id=\d*^", "", $this->postLink);

        $_GET['comment'] = isset($_GET['comment']) && isnum($_GET['comment']) ? $_GET['comment'] : 0;

    }

    /**
     * Removes comment reply
     * @param $clink
     * @return string
     */
    private static function format_clink($clink) {
        $fusion_query = array();
        $url = $url = ((array)parse_url(htmlspecialchars_decode($clink))) + array(
                'path' => '',
                'query' => ''
            );
        if ($url['query']) {
            parse_str($url['query'], $fusion_query); // this is original.
        }
        $fusion_query = array_diff_key($fusion_query, array_flip(array("comment_reply")));
        $prefix = $fusion_query ? '?' : '';
        $query = $url['path'].$prefix.http_build_query($fusion_query, NULL, '&amp;');
        return (string) $query;
    }

    /**
     * Display Comments
     * @param $comment_type
     * @param $comment_db
     * @param $comment_col
     * @param $comment_item_id
     * @param $clink
     */
    public function showComments($comment_type, $comment_db, $comment_col, $comment_item_id, $clink) {
        global $aidlink;

        $locale = fusion_get_locale();
        $locale += fusion_get_locale('', LOCALE.LOCALESET."user_fields.php");

        $cpp = $this->settings['comments_per_page'];

        $comment_data = array(
            'comment_id' => isset($_GET['comment_id']) && isnum($_GET['comment_id']) ? $_GET['comment_id'] : 0,
            'comment_name' => '',
            'comment_message' => '',
            'comment_datestamp' => time(),
            'comment_item_id' => $comment_item_id,
            'comment_type' => $comment_type,
            'comment_cat' => 0,
            'comment_ip' => USER_IP,
            'comment_ip_type' => USER_IP_TYPE,
            'comment_hidden' => 0,
        );


        /** Delete */
        if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "delete")
            && (isset($_GET['comment_id']) && isnum($_GET['comment_id']))) {
            if ((iADMIN && checkrights("C")) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS, "comment_id='".$_GET['comment_id']."' AND comment_name='".$this->userdata['user_id']."'"))) {
                $result = dbquery("
                DELETE FROM ".DB_COMMENTS."
				WHERE comment_id='".$_GET['comment_id']."'".(iADMIN ? "" : "
				AND comment_name='".$this->userdata['user_id']."'")
                );
            }
            redirect($clink.($this->settings['comments_sorting'] == "ASC" ? "" : "&amp;c_start=0"));
        }

        if ($this->settings['comments_enabled'] == "1") {

            $this->c_arr['c_info']['comments_count'] = format_word(0, $this->locale['fmt_comment']);

            // Handle Comment Posts

            if ((iMEMBER || $this->settings['guestposts'] ) && isset($_POST['post_comment'])) {

                if (!iMEMBER && $this->settings['guestposts'] ) {

                    // Process Captchas
                    $_CAPTCHA_IS_VALID = FALSE;
                    include INCLUDES."captchas/".$this->settings['captcha']."/captcha_check.php";
                    if (!isset($_POST['captcha_code']) && $_CAPTCHA_IS_VALID == FALSE) {
                        \defender::stop();
                        addNotice("danger", $locale['u194']);
                    }

                }

                $comment_data = array(
                    'comment_id' => isset($_GET['comment_id']) && isnum($_GET['comment_id']) ? $_GET['comment_id'] : 0,
                    'comment_name' => iMEMBER ? $this->userdata['user_id'] : form_sanitizer($_POST['comment_name'], '', 'comment_name'),
                    'comment_message' => form_sanitizer($_POST['comment_message'], '', 'comment_message'),
                    'comment_datestamp' => time(),
                    'comment_item_id' => $comment_item_id,
                    'comment_type' => $comment_type,
                    'comment_cat' => form_sanitizer($_POST['comment_cat'], 0, 'comment_cat'),
                    'comment_ip' => USER_IP,
                    'comment_ip_type' => USER_IP_TYPE,
                    'comment_hidden' => 0,
                );

                if (iMEMBER && (isset($_GET['c_action']) && $_GET['c_action'] == "edit") && $comment_data['comment_id']) {

                    // Update comment

                    if  ( (iADMIN && checkrights("C") ) || (iMEMBER && dbcount("(comment_id)", DB_COMMENTS, "comment_id='".$comment_data['comment_id']."'
                        AND comment_item_id='".$comment_item_id."'
                        AND comment_type='".$comment_type."'
                        AND comment_name='".$this->userdata['user_id']."'
                        AND comment_hidden='0'")) && \defender::safe() ) {

                        dbquery_insert(DB_COMMENTS, $comment_data, 'update');

                        if ($comment_data['comment_message']) {

                            $result = dbquery("UPDATE ".DB_COMMENTS." SET comment_message='".$comment_data['comment_message']."'
  									   WHERE comment_id='".$_GET['comment_id']."' ".(iADMIN ? "" : "AND comment_name='".$this->userdata['user_id']."'"));

                            if ($this->settings['comments_sorting'] == "ASC") {
                                $c_operator = "<=";
                            } else {
                                $c_operator = ">=";
                            }
                            $c_count = dbcount("(comment_id)", DB_COMMENTS, "comment_id".$c_operator."'".$comment_data['comment_id']."'
                            AND comment_item_id='".$comment_item_id."'
                            AND comment_type='".$comment_type."'");
                            $c_start = (ceil($c_count/$cpp)-1)*$cpp;
                        }

                        addNotice("success", "Comment has been updated");
                        redirect(self::format_clink($clink)."&amp;c_start=".(isset($c_start) && isnum($c_start) ? $c_start : ""));
                    }

                } else {

                    // Save New comment

                    if (!dbcount("(".$comment_col.")", $comment_db, $comment_col."='".$comment_item_id."'")) {
                        redirect(BASEDIR."index.php");
                    }

                    if (\defender::safe()) {
                        $c_start = 0;
                        $id = 0;

                        if ($comment_data['comment_name'] && $comment_data['comment_message']) {

                            require_once INCLUDES."flood_include.php";

                            if (!flood_control("comment_datestamp", DB_COMMENTS, "comment_ip='".USER_IP."'")) {

                                dbquery_insert(DB_COMMENTS, $comment_data, 'save');

                                $id = dblastid();

                                if ($this->settings['comments_sorting'] == "ASC") {
                                    $c_count = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$comment_item_id."' AND comment_type='".$comment_type."'");
                                    $c_start = (ceil($c_count/$cpp)-1)*$cpp;
                                }
                            }

                            redirect(self::format_clink($clink)."&amp;c_start=".$c_start."#c".$id);
                        }
                    }
                }
            }

            $c_rows = dbcount("(comment_id)", DB_COMMENTS, "comment_item_id='".$comment_item_id."' AND comment_type='".$comment_type."' AND comment_hidden='0'");

            if (!isset($_GET['c_start']) && $c_rows > $cpp) {
                $_GET['c_start'] = (ceil($c_rows/$cpp)-1)*$cpp;
            }

            if (!isset($_GET['c_start']) || !isnum($_GET['c_start'])) {
                $_GET['c_start'] = 0;
            }

            $comment_query = "
            SELECT tcm.*, tcu.user_id, tcu.user_name, tcu.user_avatar, tcu.user_status
            FROM ".DB_COMMENTS." tcm
            LEFT JOIN ".DB_USERS." tcu ON tcm.comment_name=tcu.user_id
            WHERE comment_item_id='".$comment_item_id."' AND comment_type='".$comment_type."' AND comment_hidden='0'
            ORDER BY comment_datestamp ".$this->settings['comments_sorting'].", comment_cat DESC";

            $query = dbquery($comment_query);

            if (dbrows($query) >0) :

                $i = ($this->settings['comments_sorting'] == "ASC" ? $_GET['c_start']+1 : $c_rows-$_GET['c_start']);

                if ($c_rows > $cpp) {
                    $this->c_arr['c_info']['c_makepagenav'] = makepagenav($_GET['c_start'], $cpp, $c_rows, 3, $clink."&amp;", "c_start");
                }

                if (iADMIN && checkrights("C")) {
                    $this->c_arr['c_info']['admin_link'] = "<!--comment_admin-->\n";
                    $this->c_arr['c_info']['admin_link'] .= "<a href='".ADMIN."comments.php".$aidlink."&amp;ctype=".$comment_type."&amp;comment_item_id=".$comment_item_id."'>".$this->locale['c106']."</a>";
                }

                while ($row = dbarray($query)) :

                    $actions = array(
                        "edit_dell" => "",
                        "edit_link" => "",
                        "delete_link" => "",
                    );

                    if ((iADMIN && checkrights("C"))
                        || (iMEMBER && $row['comment_name'] == $this->userdata['user_id'] && isset($row['user_name'])))
                    {
                        $edit_link = clean_request('c_action=edit&comment_id='.$row['comment_id'],
                                                   array('c_action', 'comment_id'), false)."#edit_comment";
                        $delete_link = clean_request('c_action=delete&comment_id='.$row['comment_id'],
                                                     array('c_action', 'comment_id'), false);
                        $comment_actions = "<!---comment_actions--><div class='btn-group'>
                        <a class='btn btn-xs btn-default' href='$edit_link'>".$this->locale['c108']."</a>
                        <a class='btn btn-xs btn-default' href='$delete_link' onclick=\"return confirm('".$this->locale['c110']."');\"><i class='fa fa-trash'></i>".$this->locale['c109']."</a>
                        </div><!---//comment_actions-->
                    ";

                        $actions = array(
                            "edit_link" => array('link'=>$edit_link, 'name'=>$this->locale['c108']),
                            "delete_link" => array('link'=>$delete_link, 'name'=>$this->locale['c109']),
                            "edit_dell" =>  $comment_actions
                        );

                    }

                    $reply_form = "";

                    if (isset($_GET['comment_reply']) && $_GET['comment_reply'] == $row['comment_id']) {

                        $locale = fusion_get_locale();
                        $comment_data['comment_cat'] = $row['comment_id'];

                        $reply_form = openform("comments_reply_form", "post", FUSION_REQUEST, array("class"=>"comments_reply_form"));

                        if (iGUEST) {
                            $reply_form .= form_text('comment_name', fusion_get_locale('c104'), $comment_data['comment_name'], array('max_length' => 30));
                        }

                        $reply_form .= form_hidden("comment_cat", "", $comment_data['comment_cat']);
                        $reply_form .= form_textarea("comment_message", "", $comment_data['comment_message'], array(
                            "tinymce" => "simple",
                            "type" => fusion_get_settings("tinymce_enabled") ? "tinymce" : "bbcode",
                            "input_id" => "comment_message-".$i,
                            "required" => true,
                        ));

                        if (iGUEST && (!isset($_CAPTCHA_HIDE_INPUT) || (isset($_CAPTCHA_HIDE_INPUT) && !$_CAPTCHA_HIDE_INPUT))) {
                            $_CAPTCHA_HIDE_INPUT = FALSE;

                            $reply_form .= "<div class='m-t-10 m-b-10'>";
                            $reply_form .= "<label class='col-xs-12 col-sm-3'>".$locale['global_150']."</label><div class='col-xs-12 col-sm-9'>\n";
                            ob_start();
                            include INCLUDES."captchas/".$this->settings['captcha']."/captcha_display.php";
                            $reply_form .= ob_get_contents();
                            ob_end_clean();

                            if (!$_CAPTCHA_HIDE_INPUT) {
                                $reply_form .= "<br />\n<label for='captcha_code'>".$locale['global_151']."</label>";
                                $reply_form .= "<br />\n<input type='text' id='captcha_code' name='captcha_code' class='textbox' autocomplete='off' style='width:100px' />\n";
                            }

                            $reply_form .= "</div>\n";
                            $reply_form .= "</div>\n";
                        }

                        $reply_form .= form_button('post_comment', $locale['c102'], $locale['c102'], array('class' => 'btn-success m-t-10'));
                        $reply_form .= closeform();
                    }

                    /** formats $row */
                    $row = array(
                        "comment_id" => $row['comment_id'],
                        "comment_cat" => $row['comment_cat'],
                        "i" => $i,
                        "user_avatar" => display_avatar($row, '50px', '', false, 'img-rounded'),
                        "user" => array(
                            "user_id" => $row['user_id'],
                            "user_name" => $row['user_name'],
                            "user_avatar" => $row['user_avatar'],
                            "status" => $row['user_status']
                        ),
                        "reply_link" => clean_request("comment_reply=".$row['comment_id'], array("comment_reply"), false),
                        "reply_form" => $reply_form,
                        "comment_datestamp" => showdate('shortdate', $row['comment_datestamp']),
                        "comment_time" => timer($row['comment_datestamp']),
                        "comment_message" => "<!--comment_message-->\n".nl2br(parseubb(parsesmileys($row['comment_message'])))."<!--//comment_message-->\n",
                        "comment_name" => $row['user_name'] ? profile_link($row['comment_name'], $row['user_name'], $row['user_status'], 'strong text-dark') : $row['comment_name'],
                    );
                    $row += $actions;

                    $id = $row['comment_id'];

                    $parent_id = $row['comment_cat'] === NULL ? "0" : $row['comment_cat'];

                    $data[$id] = $row;

                    $this->c_arr['c_con'][$parent_id][$id] = $row;

                    $this->settings['comments_sorting'] == "ASC" ? $i++ : $i--;

                endwhile;

                // Paginate the array
                $this->c_arr['c_con'][0] = array_chunk($this->c_arr['c_con'][0], $cpp, true);

                // Pass cpp settings
                $this->c_arr['c_info']['comments_per_page'] = $cpp;

                $this->c_arr['c_info']['comments_count'] = format_word(number_format($i-1, 0), $this->locale['fmt_comment']);

            endif;

            echo "<a id='comments' name='comments'></a>";
            render_comments($this->c_arr['c_con'], $this->c_arr['c_info']);
            render_comments_form($comment_type, $clink, $comment_item_id, isset($_CAPTCHA_HIDE_INPUT) ? $_CAPTCHA_HIDE_INPUT : FALSE);
        }
    }

}