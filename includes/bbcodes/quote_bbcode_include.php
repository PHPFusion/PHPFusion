<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: quote_bbcode_include.php
| Author: Core Development Team
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

$before = "<div class='quote extended'><p class='citation'>";
$endbefore = "</p><blockquote>";
$after = "</blockquote></div>";
//Broken quotes
$match = '#\[quote ((name=|post=)|name=post=|name=([\w\d ]+?)( *?)post=|name=( *?)post=([\0-9]+?))?\]#si';
//Fix broken quotes
$text = preg_replace($match, '[quote]', $text);
//Extended quotes regex
$exta = '\[quote name=([\w\d ]+?) post=([\0-9]+?)\]'; //name and post
$extb = '\[quote name=([\w\d ]+?)\]'; //name only
//Count all quotes
$qcount = substr_count($text, '[quote]');
$qcount = $qcount + preg_match_all('#'.$exta.'#si', $text, $matches);
$qcount = $qcount + preg_match_all('#'.$extb.'#si', $text, $matches);

for ($i = 0; $i < $qcount; $i++) {
    //Replace default quotes
    $text = preg_replace('#\[quote\](.*?)\[/quote\]#si', $before.$locale['bb_quote'].$endbefore.'$1'.$after, $text); //replace default quote //HTML

    //Replace extended quotes
    if (!empty($_GET['thread_id']) && isnum($_GET['thread_id'])) {
        $result = dbquery("SELECT thread_id, thread_postcount FROM ".DB_FORUM_THREADS." WHERE thread_hidden='0' AND thread_id=:id LIMIT 1", [
            ':id' => isnum($_GET['thread_id'])
        ]);
        $thread_rowstart = '';
        $inf_settings = get_settings('forum');
        if (!empty($data['thread_postcount']) && !empty($inf_settings['posts_per_page'])) {
            if ($data['thread_postcount'] > $inf_settings['posts_per_page']) {
                $thread_rowstart = $inf_settings['posts_per_page'] * floor(($data['thread_postcount'] - 1) / $inf_settings['posts_per_page']);
                $thread_rowstart = "&rowstart=".$thread_rowstart;
            }
        }

        $text = preg_replace('#'.$exta.'(.*?)\[/quote\]#si', $before.'<a class="quote-link" href="'.FORUM.'viewthread.php?thread_id='.$_GET['thread_id'].$thread_rowstart.'&pid=$2#post_$2">$1 '.$locale['bb_wrote'].': <span class="goto-post-arrow"><i class="fa fa-arrow-up"></i></span></a>'.$endbefore.'$3'.$after, $text); //replace quote with valid name and post //HTML
    }

    $text = preg_replace('#'.$extb.'(.*?)\[/quote\]#si', $before.'$1'.$endbefore.'$2'.$after, $text); //replace quote with valid name and no post //HTML
}
