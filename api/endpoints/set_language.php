<?php
defined('IN_FUSION') || exit;

$requested_language = $_POST['lang'] ?? $_GET['lang'] ?? '';
$requested_language = is_string($requested_language) ? normalize_inline_language($requested_language) : '';
$translations = get_inline_languages();
$language = isset($translations[$requested_language]) ? $requested_language : 'en-US';
$success = isset($translations[$language]);

if ($success) {
    fusion_set_cookie('site_language', $language, time() + 31536000, COOKIE_PATH, COOKIE_DOMAIN, FALSE, FALSE, 'lax');
    $_COOKIE['site_language'] = $language;
}

$format = isset($_GET['format']) && is_string($_GET['format']) ? $_GET['format'] : '';
$is_ajax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest' || $format === 'json';

if ($is_ajax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success'  => $success,
        'language' => $success ? $language : '',
    ]);
    die();
}

$redirect = $_GET['redirect'] ?? fusion_get_settings('site_path');

if (!is_string($redirect)) {
    $redirect = fusion_get_settings('site_path');
}

$redirect = htmlspecialchars_decode($redirect);

if ($redirect === '' || preg_match('#^[a-z][a-z0-9+.-]*:#i', $redirect) || strpos($redirect, '//') === 0) {
    $redirect = fusion_get_settings('site_path');
}

if ($redirect[0] !== '/') {
    $redirect = fusion_get_settings('site_path').ltrim($redirect, '/');
}

redirect($redirect);
