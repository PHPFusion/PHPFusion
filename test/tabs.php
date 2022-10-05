<?php
require_once __DIR__."/../maincore.php";
require_once FUSION_HEADER;

$tab["id"] = ["tab_1", "tab_2", "tab_3"];
$tab["title"] = ["Tab 1", "Tab 2", "Tab 3"];
$active = tab_active($tab, 1);
echo opentab($tab, $active, "tab", FALSE, "nav-tabs");
echo opentabbody($tab["title"][0], $tab["id"][0], $active);
echo lorem_ipsum(300);
echo closetabbody();
echo opentabbody($tab["title"][1], $tab["id"][1], $active);
echo lorem_ipsum(300);
echo closetabbody();
echo opentabbody($tab["title"][2], $tab["id"][2], $active);
echo lorem_ipsum(300);
echo closetabbody();
echo closetab();

require_once FUSION_FOOTER;
