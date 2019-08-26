<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

require_once INCLUDES.'captchas/lollipop/lollipop.php';
require_once INCLUDES.'captchas/lollipop/captcha_display.php';
require_once INCLUDES.'captchas/lollipop/captcha_check.php';

echo '<div class="'.grid_container().'">';
echo openform('inputform', 'post', FORM_REQUEST, ['class'=>'spacer-md']);
echo display_captcha('inputform');
echo form_button('validate', 'Bite the Lollipop', 'validate', ['class'=>'btn-primary']);
echo closeform();
echo '</div>';

require_once THEMES.'templates/footer.php';