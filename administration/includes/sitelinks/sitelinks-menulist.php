<?php

function display_sitelink_menus()
{
    if (iADMIN && checkrights("SL")) {

        $aidlink = fusion_get_aidlink();
        $locale = fusion_get_locale();

        $rowstart = (int)post("start", FILTER_VALIDATE_INT);

        $limit = (int)(post("length", FILTER_VALIDATE_INT) ?: 36);

        $search = post(["search", "value"]);

        $columns = [
            "m.menu_name LIKE '$search%'",
        ];

        $orderby = "ORDER BY m.menu_id";

        $ordering = [];
        if (!empty(post(['order']))) {
            foreach (post(['order']) as $order) {
                $column_index = $order["column"];
                if (!$column_index or $column_index == 1) {
                    $column_index = 1;
                }
                if ($column_name = post(['columns', $column_index, 'data'])) {
                    $ordering[] = form_sanitizer($column_name) . " " . form_sanitizer($order["dir"]);
                }
            }
            $orderby = "ORDER BY " . implode(",", $ordering);
        }


        $table = DB_SITE_LINK_MENUS . ' m LEFT JOIN ' . DB_SITE_LINKS . ' sl ON (sl.link_position=m.menu_id)';

        $column_sel = "m.*, count(sl.link_id) 'menu_item_count'";

        $sql_cond = '';
        if ($search) {
            $count_cond = "(" . implode(" OR ", $columns) . ")";
            $sql_cond .= " AND $count_cond";
        }

        $rowsearch = "LIMIT $rowstart, $limit";

        $list = [];

        $count_query = "SELECT " . $column_sel . " FROM " . $table . (multilang_table('SL') ? " WHERE m.menu_language='" . LANGUAGE . "'" : " WHERE") . whitespace($sql_cond) . " GROUP BY m.menu_id";

        $max_rows = dbrows(dbquery($count_query));

        $sql_query = "SELECT " . $column_sel . " FROM " . $table . (multilang_table('SL') ? " WHERE m.menu_language='" . LANGUAGE . "'" : " WHERE") . whitespace($sql_cond) . " GROUP BY m.menu_id" . whitespace($orderby) . whitespace($rowsearch);

        $result = dbquery($sql_query);

        if ($rows = dbrows($result)) {

            $i = 1;

            while ($data = dbarray($result)) {

                $admin_links = "<a class='text-muted' href='" . fusion_get_settings('siteurl') . "administration/site_links.php" . $aidlink . "&section=menu&refs=nform&action=edit&id=" . $data["menu_id"] . "'>" . $locale["edit"] . "</a> - ";
                $admin_links .= "<a class='text-danger del-warn' href='" . fusion_get_settings('siteurl') . "administration/site_links.php" . $aidlink . "&section=menu&nrefs=form&action=del&&id=" . $data["menu_id"] . "'>" . $locale["delete"] . "</a>";

                $link_name = $data["menu_name"];

                $list[] = [
                    "DT_RowId"        => $data["menu_id"],
                    "menu_checkbox"   => "<div class='display-flex'>
                    <div>" . form_checkbox("menu_id[]", "", "", ["value" => $data["menu_id"], 'input_id' => 'menu_id-' . $data["menu_id"]]) . "</div></div>",
                    "menu_name"       => "<div class='display-flex-row'><div><strong>$link_name</strong><br/>ID:" . $data["menu_id"] . " | $admin_links</div></div>",
                    "menu_item_count"      => format_num($data["menu_item_count"]),
                    'menu_grouping' => format_num($data['menu_grouping']),
                    "menu_status"     => $data["menu_status"] ? $locale['published'] : $locale['unpublished'],
                    "menu_visibility" => getgroupname($data["menu_visibility"]),
                ];

                $i++;
            }
        }

        echo json_encode(["data" => $list, "recordsTotal" => $rows, "recordsFiltered" => $max_rows, "query" => $sql_query, "responsive" => TRUE]);
    } else {
        die("Not authorized to view the information");
    }
}

/**
 * @uses display_sitelinks()
 */
fusion_add_hook("fusion_admin_hooks", "display_sitelink_menus");
