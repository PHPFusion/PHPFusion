<?php
namespace PHPFusion\UserFields\Quantum\Table;

use PHPFusion\Interfaces\TableSDK;

class Security implements TableSDK {
    
    public function data() {
        return [
            'table'      => DB_USER_FIELDS,
            'id'         => 'field_id',
            'title'      => 'field_title',
            'conditions' => "field_type='security'",
            'limit'      => 24,
        ];
        
    }
    
    public function properties() {
        return [
            'table_id'     => 'uf-security-table',
            'no_record'    => 'There are no Security Field Plugins installed.',
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
                'callback'    => [ 'PHPFusion\\UserFieldsQuantum', 'get_field_description', CLASSES.'PHPFusion/UserFieldsQuantum.inc' ],
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
     * Every row of the array is a field input.
     *
     * @return array
     */
    public function quickEdit() {
        return [
            'field_title' => [ 'title' => 'Field Name', 'required' => TRUE, 'function' => 'form_text' ],
            'field_order' => [ 'title' => 'Field Order', 'function' => 'form_textarea' ],
        ];
    }
}

