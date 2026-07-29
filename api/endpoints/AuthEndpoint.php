<?php

namespace PHPFusion\Api;

use PHPFusion\Authenticate;
use Throwable;

final class AuthEndpoint
{
    private const ADMIN_FORM_ID = 'admin-login-form';
    private const MEMBER_FORM_ID = 'loginpageFrm';

    public static function adminLogin(ApiRequest $request): ApiResponse
    {
        $locale = fusion_get_locale();

        if (!iADMIN) {
            return self::authJson(401, FALSE, $locale['cookie_title'], $locale['global_183']);
        }

        if (!fusion_safe()) {
            return self::authJson(403, FALSE, $locale['error_request'], $locale['token_error']);
        }

        $password = (string)sanitizer('admin_password');
        if ($password === '' || !Authenticate::validateAuthAdmin($password)) {
            return self::authJson(
                422,
                FALSE,
                $locale['password_invalid'],
                $locale['password_invalid_description']
            );
        }

        if (!Authenticate::setAdminCookie($password)) {
            return self::authJson(
                500,
                FALSE,
                $locale['cookie_error'],
                $locale['cookie_error_description']
            );
        }

        unset($_SESSION['notices']);
        addnotice('success', $locale['global_186'], 'index.php');

        return self::authJson(200, TRUE, '', '', ADMIN . 'index.php' . fusion_get_aidlink());
    }

    public static function memberIdentity(ApiRequest $request): ApiResponse
    {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();

        if (iMEMBER) {
            return self::memberJson(200, TRUE, '', BASEDIR . $settings['opening_page']);
        }

        if (!fusion_safe()) {
            return self::memberJson(403, FALSE, $locale['token_error']);
        }

        $loginUser = trim((string)sanitizer('login_user'));
        $loginMethod = (int)$settings['login_method'];
        $fieldLabel = match ($loginMethod) {
            1       => $locale['global_107'],
            2       => $locale['global_107'] . '/' . $locale['global_101'],
            default => $locale['global_101'],
        };

        if ($loginUser === '') {
            return self::memberJson(422, FALSE, $locale['error_input_default']);
        }

        $identityColumn = match ($loginMethod) {
            1       => 'user_email',
            2       => filter_var($loginUser, FILTER_VALIDATE_EMAIL) ? 'user_email' : 'user_name',
            default => 'user_name',
        };

        $identity = strtolower($loginUser);
        $result = dbquery(
            "SELECT user_name, user_email FROM " . DB_USERS . " WHERE " . $identityColumn . "=:identity LIMIT 1",
            [':identity' => $identity]
        );

        if (!dbrows($result)) {
            return self::memberJson(422, FALSE, $fieldLabel . ' is not found');
        }

        $user = dbarray($result);
        $verifiedIdentity = $identityColumn === 'user_email' ? $user['user_email'] : $user['user_name'];
        $redirect = BASEDIR . 'login.php?' . http_build_query([
            'authName'    => $verifiedIdentity,
            'remember_me' => check_post('remember_me') ? 1 : 0,
        ]);

        return self::memberJson(200, TRUE, '', $redirect);
    }

    public static function memberPassword(ApiRequest $request): ApiResponse
    {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $redirect = BASEDIR . $settings['opening_page'];

        if (iMEMBER) {
            return self::memberJson(200, TRUE, '', $redirect);
        }

        if (!fusion_safe()) {
            return self::memberJson(403, FALSE, $locale['token_error']);
        }

        $loginUser = trim((string)sanitizer('user_name'));
        $password = (string)sanitizer('user_pass');
        if ($loginUser === '' || $password === '') {
            return self::memberJson(422, FALSE, $locale['password_invalid_description']);
        }

        try {
            $auth = new Authenticate();
            $authenticated = $auth->authenticate(
                $loginUser,
                $password,
                check_post('remember_me')
            );
        } catch (Throwable) {
            return self::memberJson(500, FALSE, $locale['error_request']);
        }

        if (!$authenticated) {
            $message = $locale['password_invalid_description'];
            $notices = getnotices(FUSION_SELF);

            foreach (['danger', 'warning'] as $status) {
                if (!empty($notices[$status][0])) {
                    $noticeMessage = trim(strip_tags($notices[$status][0]));
                    if ($noticeMessage !== $locale['error_input_login']) {
                        $message = $noticeMessage;
                    }
                    break;
                }
            }

            remove_notice();
            return self::memberJson(422, FALSE, $message);
        }

        unset($_SESSION['notices']);
        $noticePath = parse_url(htmlspecialchars_decode($redirect), PHP_URL_PATH);
        $noticeKey = basename(is_string($noticePath) ? $noticePath : '') ?: 'index.php';
        addnotice('success', $locale['global_186'], $noticeKey);

        return self::memberJson(200, TRUE, '', $redirect);
    }

    private static function authJson(
        int $status,
        bool $success,
        string $title,
        string $message,
        string $redirect = ''
    ): ApiResponse {
        return ApiResponse::json([
            'success'  => $success,
            'title'    => $title,
            'message'  => $message,
            'token'    => $success ? '' : fusion_get_token(self::ADMIN_FORM_ID),
            'redirect' => $redirect,
        ], $status);
    }

    private static function memberJson(
        int $status,
        bool $success,
        string $message,
        string $redirect = ''
    ): ApiResponse {
        return ApiResponse::json([
            'success'  => $success,
            'message'  => $message,
            'token'    => $success ? '' : fusion_get_token(self::MEMBER_FORM_ID),
            'redirect' => $redirect,
        ], $status);
    }
}
