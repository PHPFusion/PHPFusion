<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

require_once INCLUDES.'captchas/lollipop/captcha_display.php';

echo openform('inputform', 'post');
echo display_captcha('inputform');
echo closeform();

require_once THEMES.'templates/footer.php';