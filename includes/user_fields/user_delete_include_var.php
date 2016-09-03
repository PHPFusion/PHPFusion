<?php
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Version of the user fields api
$user_field_api_version = "1.01.00";
$user_field_name = $locale['uf_delete'];
$user_field_desc = $locale['uf_delete_desc'];
$user_field_dbname = "user_delete";
$user_field_group = 1;
$user_field_dbinfo = "VARCHAR(2) NOT NULL DEFAULT ''";