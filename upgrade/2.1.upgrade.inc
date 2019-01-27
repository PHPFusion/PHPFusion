<?php
$inf_version = '2.1';
$inf_newtable[] = DB_FORUM_REPORTS." (
    report_id MEDIUMINT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    report_post_id MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
    report_user MEDIUMINT(11) UNSIGNED NOT NULL DEFAULT '0',
    report_comment TEXT NOT NULL,
    report_mod_comment TEXT NOT NULL,
    report_status SMALLINT(1) NOT NULL DEFAULT '0',    
    report_datestamp INT(10) UNSIGNED NOT NULL DEFAULT '0',    
    report_updated INT(10) UNSIGNED NOT NULL DEFAULT '0',
    report_archive TEXT NOT NULL,    
    PRIMARY KEY (report_id)
) ENGINE=MyISAM DEFAULT CHARSET=UTF8 COLLATE=utf8_unicode_ci";
