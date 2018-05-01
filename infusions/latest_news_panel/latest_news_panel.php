<?php
// double section
// do not use container
require_once INFUSIONS.'news/classes/autoloader.php';
$news = \PHPFusion\News\NewsServer::News();
$news_result = $news->get_NewsItem(['order'=>'tn.news_datestamp DESC', 'limit' => 1]);
if (!empty($news_result)) {
    $news_item = $news_result['news_items'][1];
}
?>
<!--showcase-->
<div class='lp-wrapper clearfix'>
    <div style='height:600px; background-image: url(<?php echo $news_item['news_image_src'] ?>); background-size:cover;'></div>
    <div class='lp-excepts'>
        <div class='lp-left'>
            <i class='fa fa-stack'>
                <i class='fa fa-circle fa-stack-2x'></i>
                <i class='fa fa-caret-right fa-stack-1x'></i>
            </i>
            <h4>READ MORE</h4>
        </div>
        <div class='lp-text'>
            <h1><a href='<?php echo $news_item['news_url'] ?>'><?php echo $news_item['news_subject'] ?></a></h1>
            <span class='comments'><?php echo format_word($news_item['news_comments'], fusion_get_locale('fmt_comment')) ?></span>
            <span class='author'>by <?php echo profile_link($news_item['user_id'], $news_item['user_name'], $news_item['user_status']) ?></span>
            <span class='date'><strong>/</strong> <?php echo showdate('newsdate', $news_item['news_date']) ?></span>
            <p><?php echo lorem_ipsum(300) ?></p>
        </div>
    </div>
</div>
<!--//showcase-->
