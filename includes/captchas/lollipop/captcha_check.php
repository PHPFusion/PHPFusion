<?php
defined('IN_FUSION') || exit;

$captcha = new Lollipop(post('form_id'));

$_CAPTCHA_IS_VALID = $captcha->validateCaptcha();

if ($_CAPTCHA_IS_VALID !== NULL) {
    if ($_CAPTCHA_IS_VALID === TRUE) {
        addNotice('success', 'Captcha is validated');
    } else {
        addNotice('danger', 'Captcha is invalid');
    }
}
