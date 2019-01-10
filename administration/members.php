<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: members.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__.'/../maincore.php';
require_once THEMES."templates/admin_header.php";
require_once ADMIN.'members/members_administration.php';
// members tab css required.
echo "<style>
.nav-tabs.nav-stacked {
    width: 20%;
    float: left;
    border: 0;
}
.nav-tabs.nav-stacked > li:first-child > a {
    border-top: 0;
}
.nav-tabs.nav-stacked > li.active > a, .nav-tabs.nav-stacked > li.active > a:hover, .nav-tabs.nav-stacked > li.active > a:focus {
    background-color: #ededed;
    margin: 0;
    color: #444;
    border: 0;
    line-height: 1.5;
    border-top: 1px solid #e5e5e5;
    border-bottom: 1px solid #e5e5e5;
}
.nav-tabs.nav-stacked > li > a {
    border: 0;
    border-top: 1px solid #e5e5e5;
    border-bottom: 1px solid #e5e5e5;
    line-height: 1.5;
    background: #fafafa;
    padding: 10px 15px;
    -webkit-border-radius: 0;
    -moz-border-radius: 0;
    border-radius: 0;
}
.nav-tabs.nav-stacked ~ .tab-content {
    width: 80%;
    float: left;
    padding: 0;
    border: 0;
    border-left: 1px solid #e5e5e5;
    padding: 20px;
}
.nav-tabs.nav-stacked > li {
    margin-right:0;
}
</style>";
Administration\Members\Members_Admin::getInstance()->display_admin();
require_once THEMES."templates/footer.php";
