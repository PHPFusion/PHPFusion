<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: quote_bbcode_include.php
| Author: JoiNNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
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
    $text = preg_replace('#'.$exta.'(.*?)\[/quote\]#si', $before.'<a class=\'quote-link\' rel=\'$2\' href=\'viewthread.php?pid=$2\' target=\'_blank\'>$1 '.$locale['bb_wrote'].': <span class=\'goto-post-arrow\'>&uarr;</span></a>'.$endbefore.'$3'.$after, $text); //replace quote with valid name and post //HTML
    $text = preg_replace('#'.$extb.'(.*?)\[/quote\]#si', $before.'$1'.$endbefore.'$2'.$after, $text); //replace quote with valid name and no post //HTML
}

// Add once only
if (!defined('bbcode_quote_js')) {
    define('bbcode_quote_js', true);
    if (function_exists('add_to_footer')) {
        add_to_footer("<script type='text/javascript'>".jsminify("/* <![CDATA[ */
        jQuery(document).ready(function() {
        /*!
         * Extended Quote BBcode for PHP-Fusion
         * with jQuery Quote Collapse
         *
         * Author: JoiNNN
         *
         * Copyright (c) 2002 - 2012 by Nick Jones
         * Released as free software without warranties under GNU Affero GPL v3. 
         */        
        var quoteColHeight	= 184,			//0 - disables quote collapse
            colCls			= 'collapsed',	//class when collapsed
            expCls			= 'expanded';	//class when expanded
        
        if (quoteColHeight > 0) {
        // On page load
        jQuery('.quote').each(function() {
            var quote		= jQuery(this),
                block		= quote.find('.blockquote').first();
        
            //On load add expand link if quote is long enough
            if (block.height() > quoteColHeight) {
                quote.addClass(colCls);
                quote.find('.citation').first().prepend('<a href=\"#\" class=\"toggle-quote ' + colCls + ' flright\">".$locale['bb_quote_expand']."</a>');
                block.css({'height': quoteColHeight, 'overflow': 'hidden'});
            }
        });
        
        // On click
        jQuery('.toggle-quote').click(function(e) {
            e.preventDefault();
            var toggler		= jQuery(this),
                quote		= toggler.parent().parent(),
                block		= quote.find('.blockquote').first();
        
            if (block.height() > quoteColHeight) {
                block.stop().animate({'height': quoteColHeight + 'px'}, 200);
                toggler.html('".$locale['bb_quote_expand']."');
                toggler.removeClass(expCls).addClass(colCls);
                quote.removeClass(expCls).addClass(colCls);
            } else {
                block.stop().animate({'height': block[0].scrollHeight + 'px'}, 200, function() {
                    jQuery(this).css({'height': 'auto'});
                });
                toggler.html('".$locale['bb_quote_collapse']."');
                toggler.removeClass(colCls).addClass(expCls);
                quote.removeClass(colCls).addClass(expCls);
            }
        });
        
        // Scroll to quoted post if is on the same page as the quote, instead of opening a new tab
        jQuery('.quote-link').click(function(e) {
            var pid = jQuery(this).attr('rel');
            if (jQuery('#post_' + pid).length) {
                var target =jQuery('#post_' + pid).offset().top;
                jQuery('html, body').animate({scrollTop:target}, 200);
                e.preventDefault();
            }
        });
        
        }
        });/* ]]> */
        ")."</script>
        ");
    }
}