<?php
require_once dirname(__FILE__).'/maincore.php';
require_once THEMES.'templates/header.php';
require_once BASEDIR.'classifieds/autoload.php';
\PHPFusion\Panels::getInstance()->hide_panel('LEFT');
\PHPFusion\Panels::getInstance()->hide_panel('RIGHT');
ThemeFactory\Core::setParam('headerBg_class', 'headerBg seeThrough');
ThemeFactory\Core::setParam('body_container', FALSE);

ob_start();
echo openbanner([
        'background' => IMAGES.'page/box.png',
        'height' => '500px',
]);
?>
    <h1 class='m-0 text-white text-light'>Welcome to Farlayne Classifieds</h1>
    <h5 class='text-white text-light'>Search Farlayne to eat, drink, shop, or visit in any city in the world.</h5>
    <div class='container'>
        <?php
        echo openform('search_frm', 'post', FUSION_REQUEST, ['inline' => TRUE, 'class' => 'banner-form spacer-sm']);
        echo form_text('search_txt', '', '', ['placeholder' => 'Im looking for', 'inner_class' => 'inverted input-lg', 'feedback_icon' => TRUE, 'icon' => 'fa-caret-down fa fa-lg']);
        echo form_text('search_loc', '', 'Kota Kinabalu', ['inner_class' => 'inverted input-lg']);
        echo form_button('search_submit', 'Search', '', ['class' => 'btn-primary btn-lg']);
        echo closeform();
        ?>
    </div>
<?php
echo closebanner();
\ThemeFactory\Core::setParam('subheader_content', ob_get_clean());
?>

<div class='category'>
    <div class='container'>
        <h1 class='text-center text-light spacer-lg'>Discover on Common Interests
        <span>Explore and join any communities by the categories of your interest.</span>
        </h1>
        <div class='spacer-lg'>
            <?php
            $category = new \Classifieds\View\Categories();
            echo $category->display_main_categories();
            ?>
        </div>
    </div>
</div>
<div class='location'>
    <div class='container'>

    </div>
</div>






<?php
require_once THEMES.'templates/footer.php';