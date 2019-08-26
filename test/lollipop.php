<?php
require_once __DIR__.'/../maincore.php';
require_once THEMES.'templates/header.php';

if (post('validate')) {
    $_CAPTCHA_IS_VALID = FALSE;
    include INCLUDES.'captchas/lollipop/captcha_check.php';

    if (!$_CAPTCHA_IS_VALID) {
        addNotice('danger', 'Captcha is invalid');
    } else {
        addNotice('success', 'Captcha is validated');
    }

    if (\Defender::safe()) {
        redirect(FUSION_SELF);
    }
}

echo openform('inputform', 'post', FORM_REQUEST, ['class'=>'spacer-md']);
include INCLUDES.'captchas/lollipop/captcha_display.php';
echo display_captcha([
    'form_name' => 'inputform'
]);
echo form_button('validate', 'Bite the Lollipop', 'validate', ['class'=>'btn-primary']);
echo closeform();

require_once THEMES.'templates/footer.php';
