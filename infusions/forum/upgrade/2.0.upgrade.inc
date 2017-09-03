<?php
if (!dbcount("(settings_name)", DB_INFUSIONS, "settings_name='forum_show_lastpost'")) {
    $inf_insertdbrow[] = DB_INFUSIONS." (settings_name, settings_value, settings_inf) VALUES ('forum_show_lastpost', '1', 'forum')";
}
if (!dbcount("(settings_name)", DB_INFUSIONS, "settings_name='forum_enabled_userfields'")) {
    $inf_insertdbrow[] = DB_INFUSIONS." (settings_name, settings_value, settings_inf) VALUES ('forum_enabled_userfields', '', 'forum')";
}