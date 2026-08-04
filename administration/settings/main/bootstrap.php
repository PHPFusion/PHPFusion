<?php

defined('IN_FUSION') || exit;

require_once INCLUDES.'classes/PHPFusion/Administration/Page/AdminPage.php';
require_once INCLUDES.'classes/PHPFusion/Administration/Api/AdminApiResponse.php';
require_once INCLUDES.'classes/PHPFusion/Administration/Api/AdminApiGuard.php';
require_once INCLUDES.'classes/PHPFusion/Administration/Api/AdminApiCache.php';
require_once __DIR__.'/MainSettingsSchema.php';
require_once __DIR__.'/SettingsMainRepository.php';
require_once __DIR__.'/SettingsMainService.php';
require_once __DIR__.'/SettingsMainEndpoint.php';
require_once __DIR__.'/MainSettingsPage.php';
