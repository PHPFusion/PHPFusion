<?php
defined('IN_FUSION') || exit;

$_CAPTCHA_HIDE_INPUT = TRUE;

if (!function_exists('display_captcha')) {

    /**
     * Displays Lollipop Captcha
     * @param      $form_name
     * @param bool $inline_options
     * @param bool $inline
     *
     * @throws Exception
     */
    function display_captcha($form_name, $inline = TRUE, $inline_options = TRUE) {
        $captcha = new Lollipop($form_name);
        // $post_value = sanitizer(['lollipop'], '', 'lollipop'); // most captcha do not allow retained post values.
        echo form_checkbox('lollipop[]', $captcha->getQuestions(), '', [
            'options'        => $captcha->getAnswers(),
            'inline_options' => $inline_options,
            'inline'         => $inline,
            'required'       => TRUE,
        ]);
    }
}
