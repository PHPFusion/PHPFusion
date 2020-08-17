<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: Prefference.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\UserFields\Quantum\Table;

use PHPFusion\Interfaces\TableSDK;
use SqlHandler;

class Preference implements TableSDK {

    public function data() {
        return [
            'table'      => DB_USER_FIELDS,
            'id'         => 'field_id',
            'title'      => 'field_title',
            'conditions' => "field_section='preferences' AND field_type='file'",
            'limit'      => 24,
        ];
    }

    public function properties() {
        return [
            'table_id'     => 'uf-preference-table',
            'no_record'    => 'There are no Preference Field Plugins installed.',
            'search_label' => 'Search User Fields',
            'search_col'   => 'field_title',
            'order_col'    => [ 'field_title' => 'title', 'field_order' => 'order' ],
        ];
    }

    public function column() {
        return [
            'field_title' => [
                'title'       => 'User Field',
                'title_class' => 'col-xs-11',
                'value_class' => 'no-break',
                'callback'    => [ 'PHPFusion\\UserFieldsQuantum', 'getUserFieldDescription', CLASSES.'PHPFusion/UserFieldsQuantum.inc' ],
                'edit_link'   => FALSE,
                'delete_link' => FALSE,
            ],
            'field_order' => [
                'title'       => 'Order',
                'title_class' => 'col-xs-2',
            ],
            'field_id'    => [
                'title' => 'ID',
            ],
        ];
    }

    /**
     * @return array
     */
    public function quickEdit() {
        return [
            //'field_title' => [ 'title' => 'Field Name', 'required' => TRUE ],
            'field_order' => [ 'label' => 'Field Order', 'function' => 'form_textarea' ],
        ];
    }

    public function bulkDelete( $data ) {
        if ( fusion_safe() ) {
            if ( column_exists( DB_USERS, $data['field_name'] ) ) {
                SqlHandler::drop_column( DB_USERS, $data['field_name'] );
            }
        }
    }

}
