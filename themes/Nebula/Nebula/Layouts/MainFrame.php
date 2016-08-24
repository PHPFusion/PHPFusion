<?php
namespace Nebula\Layouts;

use Nebula\NebulaTheme;
use PHPFusion\Panels;

class MainFrame extends NebulaTheme {

    protected static $boxed_Content = TRUE;

    public function __construct() {
        if ($this->getParam('header') === TRUE) {
            $this->NebulaHeader();
        }

        $this->NebulaBody();

        if ($this->getParam('footer') === TRUE) {
            $this->NebulaFooter();
        }
    }

    private function NebulaHeader() {
        echo renderNotices(getNotices(array('all', FUSION_SELF)));
        ?>
        <header>

        <div class="headerInner">
                    <div class="container">
                        <div class="row">
                            <div class="col-xs-12 col-sm-3">
                                <div class="logo">
                                    <a href="<?php echo BASEDIR.fusion_get_settings('opening_page') ?>"
                                       title="<?php echo fusion_get_settings('site_name') ?>">
                                        <img src="<?php echo BASEDIR.fusion_get_settings('sitebanner') ?>" alt=""/>
                                    </a>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-9">
                                <div class="navbar-header navbar-right">
                                    <ul class="navbar-nav">
                                        <?php if (iMEMBER) : ?>
                                            <li><a href="<?php echo BASEDIR."members.php" ?>">Members</a></li>
                                            <?php if (iADMIN) : ?>
                                                <li><a href="<?php echo ADMIN."index.php".fusion_get_aidlink() ?>" title="Administration Panel">Administration
                                                        Panel</a></li>
                                            <?php endif; ?>
                                            <li><a href="<?php echo FUSION_SELF."?logout=yes" ?>">Logout</a></li>
                                        <?php else: ?>
                                            <li><a href="<?php echo BASEDIR."register.php" ?>">Register</a></li>
                                            <li><a href="<?php echo BASEDIR."login.php" ?>">Login</a></li>
                                        <?php endif; ?>
                                        <li><a href="">Set your language</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php echo showsublinks('', '', array(
                            'class' => 'navbar-default',
                            'links_per_page' => 8,
                            'links_grouping' => TRUE,
                        )) ?>
                        <?php if (AU_CENTER) : ?>
                            <div class="headerContent">
                                <?php echo AU_CENTER; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

        </header>
        <?php
    }

    private function NebulaBody() {
        ?>
        <?php if (U_CENTER) : ?>
            <section class="nebulaContentTop">
                <div class="container">
                    <?php echo U_CENTER; ?>
                </div>
            </section>
        <?php endif; ?>
        <?php
        $side_span = 2;
        $main_span = 12;
        if (RIGHT) {
            if (RIGHT) {
                $main_span = $main_span - $side_span;
            }
        }
        ?>

        <?php if (LEFT) : ?>
            <div class="nebulaCanvas off">
                <a class='canvas-toggle' href='#' data-target='nebulaCanvas'>
                    <i class='fa fa-bars fa-lg'></i>
                </a>
                <?php echo LEFT ?>
            </div>
            <?php
            add_to_jquery("
            $('.canvas-toggle').bind('click',function(){
             var target = $(this).data('target');
             $('.'+target).toggleClass('off');
            });
            ");

            ?>
        <?php endif; ?>

        <?php if (self::$boxed_Content === TRUE) : ?>
        <section class="nebulaBody">
            <div class="container">
        <?php endif; ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-<?php echo $main_span ?>">
                        <?php echo CONTENT ?>
                    </div>
                    <?php if (RIGHT) : ?>
                        <div class="col-xs-12 col-sm-<?php echo $side_span ?>">
                            <?php echo RIGHT ?>
                        </div>
                    <?php endif; ?>
                </div>
        <?php if (self::$boxed_Content === TRUE) : ?>
            </div>
        </section>
        <?php
        endif;
    }

    private function NebulaFooter() {
        ?>
        <section class="nebulaFooter">
            <div class="container">
                <?php echo stripslashes(strip_tags(fusion_get_settings('footer'))) ?>
                <p><?php echo showcopyright() ?></p>
                <?php if (fusion_get_settings('visitorcounter_enabled')) : echo "<p>".showcounter()."</p>\n"; endif; ?>
                <p>Nebula Theme by <a href='https://www.php-fusion.co.uk/profile.php?lookup=16331' target='_blank'>Chan</a></p>
                <a href="#top" class="pull-right"><i class="fa fa-arrow-circle-o-up fa-3x"></i></a>

                <p>
                    <?php
                    if (fusion_get_settings('rendertime_enabled') == '1' || fusion_get_settings('rendertime_enabled') == '2') :
                        echo showrendertime();
                        echo showMemoryUsage();
                    endif;
                    $footer_errors = showFooterErrors();
                    if (!empty($footer_errors)) : echo $footer_errors; endif;
                    ?>
                </p>

            </div>
        </section>
        <?php
    }
}