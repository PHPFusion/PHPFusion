<?php

use PHPFusion\Social\SocialNotifications;

register_local_task(
    'social_notification',
    [SocialNotifications::class, 'deliver'],
    priority: 10
);
