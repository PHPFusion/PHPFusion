<?php
namespace Nebula\Templates;

use Nebula\Layouts\MainFrame;
use Nebula\NebulaTheme;
use PHPFusion\Panels;

class Page extends MainFrame {

    public static function display_page($info) {
        //echo render_breadcrumbs();
        NebulaTheme::getInstance()->setParam('container', FALSE);
        if (isset($_GET['viewpage']) && $_GET['viewpage'] == 1) {
            NebulaTheme::getInstance()->setParam('headerBg', FALSE);
        }
        Panels::getInstance(TRUE)->hide_panel('RIGHT');
        ?>
        <!--cp_idx-->
        <?php if (!empty($info['error'])) : ?>
            <div class="well text-center">
                <?php echo $info['error'] ?>
            </div>
        <?php else: ?>
            <?php echo $info['body']; ?>
        <?php endif; ?>
        <?php
        }

}