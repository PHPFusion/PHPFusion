<?php
defined('IN_FUSION') || exit;

function bootstrap4() {
    $settings = fusion_get_settings();
    $text_direction = fusion_get_locale('text-direction');

    if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
        $boilerplate = BOILERPLATES;
        if (!empty('CDN')) {
            $boilerplate = CDN.'themes/boilerplates/';
        }
        ?>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo $boilerplate.'bootstrap4/css/bootstrap.min.css' ?>">
        <?php add_to_footer('<script src="'.$boilerplate.'bootstrap4/js/bootstrap.bundle.min.js"></script>'); ?>
        <?php if ($text_direction == 'rtl') : ?>
            <link rel="stylesheet" href="<?php echo $boilerplate.'bootstrap4/css/bootstrap-rtl.min.css' ?>">
        <?php endif; ?>
        <?php if ($settings['devmode']) : ?>
            <link rel="stylesheet" href="<?php echo $boilerplate.'bootstrap4/css/bootstrap.min.css.map' ?>">
            <?php add_to_footer('<script src="'.$boilerplate.'bootstrap4/js/bootstrap.bundle.min.js.map"></script>'); ?>
            <?php if ($text_direction == 'rtl') : ?>
                <link rel="stylesheet" href="<?php echo $boilerplate.'bootstrap4/css/bootstrap-rtl.css.map' ?>">
            <?php endif ?>
        <?php endif;
    }
}

function change_to_twig() {
    return [
        'nav'      => [
            'nav_path'        => BOILERPLATES.'bootstrap4/html/navbar/navbar.html',
            'nav_li_no_link'  => BOILERPLATES.'bootstrap4/html/navbar/nav_li_no_link.html',
            'nav_li_link'     => BOILERPLATES.'bootstrap4/html/navbar/nav_li_link.html',
            'nav_li_dropdown' => BOILERPLATES.'bootstrap4/html/navbar/nav_li_dropdown.html',
            'nav_divider'     => BOILERPLATES.'bootstrap4/html/navbar/nav_li_divider.html',
        ],
        'modal'    => BOILERPLATES.'bootstrap4/html/modal.html',
        'progress' => BOILERPLATES.'bootstrap4/html/progress.html'
    ];
}
