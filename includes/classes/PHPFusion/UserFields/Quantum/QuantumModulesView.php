<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: QuantumModulesView.php
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
namespace PHPFusion\UserFields\Quantum;

use PHPFusion\UserFieldsQuantum;

class QuantumModulesView {

    protected $cat_list = [];
    protected $page_list = [];
    private $available_field_info = [];
    private $modules = [];
    private $class;


    public function __construct( UserFieldsQuantum $class ) {
        $this->class = $class;
        $this->cat_list = $this->class->getCatList();
        $this->modules = $this->class->getModules();
        $this->page_list = $this->class->getPageList();
        $this->available_field_info = $this->class->getAvailableFieldInfo();
    }

    /**
     * @param string $folder
     *
     * @return string
     * @throws \Exception
     * @todo: REST API module load
     */
    public function viewModules( $folder = 'public' ) {
        $aidlink = fusion_get_aidlink();
        $pf_html = '';
        $no_modules = '<div class="strong text-center">There are no user fields available</div>';
        if ( !empty( $this->cat_list ) && $folder == 'public' ) {
            $field_type = $this->class->get_dynamics_type();
            unset( $field_type['file'] );
            $pf_html .= '<div class="list-group-item"><div class="row equal-height">';
            foreach ( $field_type as $type => $name ) {
                $pf_html .= '<div class="'.grid_column_size( 50, 50, 20 ).' p-b-20"><a class="btn btn-block btn-default" href="'.ADMIN.'user_fields.php'.$aidlink.'&amp;ref=public&amp;action=new&amp;add_field='.$type.'">'.$name.'</a></div>';
            }
            $pf_html .= '</div></div><hr/>';
        }
        // modules
        if ( !empty( $this->modules ) ) {

            $no_modules = '';

            $this->doModuleAction( $folder );

            $pf_html .= '<div class="row equal-height">';

            foreach ( $this->modules as $module_name => $module_data ) {
                $button = "<a class='btn btn-default' href='".clean_request( "install=".$module_name, [ 'install', 'uninstall' ], FALSE )."'>Install</a>";
                if ( !in_array( $module_name, array_keys( $this->available_field_info ) ) ) {
                    $button = "<a class='btn btn-danger' href='".clean_request( "uninstall=".$module_name, [ 'install', 'uninstall' ], FALSE )."'>Uninstall</a>";
                }
                $image = '';
                if ( $module_data['module_image'] ) {
                    $image = "<img src='".$module_data['module_image']."' class='icon img-responsive' style='margin:15px auto; max-height:48px;'>";
                }

                $pf_html .= '<div class="m-b-15 '.grid_column_size( 100, 50, 33, 25 ).'">
                <div class="list-group-item text-center display-flex-column">'.$image.'
                <h4 class="strong">'.$module_data['user_field_name'].'</h4>
                <div class="clearfix position-relative overflow-hide m-b-20">'.$module_data['user_field_desc'].'<br/><small class="strong">Version: '.$module_data['user_field_version'].'</small></div>
                <div class="text-right" style="margin-top:auto; padding-top:10px; border-top:1px solid #ddd;">'.$button.'</div>
                </div>
                </div>';
            }

            $pf_html .= '</div>';
        }
        $pf_html .= $no_modules;

        return (string)$pf_html;
    }

    /**
     * Do module installation
     *
     * @param $folder
     *
     * @throws \Exception
     */
    private function doModuleAction( $folder ) {
        // 1 click uninstall modules
        if ( $install = get( 'install' ) ) {

            if ( $folder == 'public' ) {

                $modal = openmodal( 'modadd', "<h4 class='m-0'>Add User Fields Module</h4>" );
                $modal .= $this->viewModulesForm();
                $modal .= closemodal();
                add_to_footer( $modal );

            } else {
                // Install for preferences and security
                if ( isset( $this->modules[ $install ] ) ) {

                    if ( in_array( $install, array_keys( $this->available_field_info ) ) ) {

                        $module_data = $this->modules[ $install ];

                        $module = [
                            'field_id'           => 0,
                            'field_title'        => $module_data['user_field_name'],
                            'field_name'         => $module_data['user_field_dbname'],
                            'field_cat'          => 0,
                            'field_type'         => 'file',
                            'field_default'      => '',
                            'field_error'        => '',
                            'field_required'     => 0,
                            'field_log'          => 0,
                            'field_registration' => 0,
                            'field_order'        => 0,
                            'field_config'       => 0,
                            'field_section'      => $folder
                        ];

                        $module['field_order'] = dbresult( dbquery( "SELECT COUNT(field_id) FROM ".DB_USER_FIELDS." WHERE field_type=:type AND field_cat=:cat_id", [ ':cat_id' => $module['field_cat'], ':type' => $folder ] ), 0 ) + 1;
                        if ( $this->class->updateFields( $module, 'module', DB_USERS, $this->modules ) ) {
                            redirect( clean_request( '', [ 'install' ], FALSE ) );
                        }
                    }
                }
            }
        } else if ( $uninstall = get( 'uninstall' ) ) {

            if ( isset( $this->modules[ $uninstall ] ) ) {
                $module = $this->modules[ $uninstall ];
                $field_id = (int)dbresult( dbquery( "SELECT field_id FROM ".DB_USER_FIELDS." WHERE field_name=:name AND field_type=:file AND field_section=:folder", [
                    ':name'   => $module['user_field_dbname'],
                    ':file'   => 'file',
                    ':folder' => $folder
                ] ), 0 );
                if ( $this->class->removeField( $field_id ) ) {
                    redirect( clean_request( '', [ 'uninstall' ], FALSE ) );
                }
            }
        }

    }

    /** Modules Form */
    public function viewModulesForm() {
        $locale = fusion_get_locale();
        $aidlink = fusion_get_aidlink();
        $field_data = [];
        $install_plugin = get( 'install' );
        $action = get( 'action' );
        $module_id = get( 'module_id', FILTER_VALIDATE_INT );
        $ref_module = get( 'ref' );
        if ( $action == 'module_edit' && $module_id ) {
            $result = dbquery( "SELECT * FROM ".DB_USER_FIELDS." WHERE field_id=:mid", [ ':mid' => (int)$module_id ] );
            if ( dbrows( $result ) ) {
                $field_data = dbarray( $result );
                $install_plugin = $field_data['field_name'];
            } else {
                add_notice( 'info', $locale['field_0205'] );
                redirect( FUSION_SELF.$aidlink );
            }
        }

        if ( isset( $this->modules[ $install_plugin ] ) ) {

            $plugin_data = $this->modules[ $install_plugin ];

            // 1 click Install Modules
            if ( post( 'enable' ) ) {

                $accepted_type = [
                    'public'      => 'file',
                    'preferences' => 'file',
                    'security'    => 'file'
                ];

                if ( isset( $accepted_type[ $ref_module ] ) ) {
                    $field_data = [
                        'add_module'         => sanitizer( 'add_module' ) ?: $field_data['field_name'],
                        'field_type'         => $accepted_type[ $ref_module ],
                        'field_id'           => sanitizer( 'field_id', '', 'field_id' ) ?: get( 'module_id', FILTER_VALIDATE_INT ) ?: 0,
                        'field_title'        => sanitizer( 'field_title', '', 'field_title' ),
                        'field_name'         => sanitizer( 'field_name', '', 'field_name' ),
                        'field_cat'          => sanitizer( "field_cat", '', 'field_cat' ),
                        'field_default'      => sanitizer( "field_default", '', 'field_default' ),
                        'field_error'        => sanitizer( "field_error", '', 'field_error' ),
                        'field_required'     => post( "field_required" ) ? 1 : 0,
                        'field_registration' => post( "field_registration" ) ? 1 : 0,
                        'field_log'          => post( "field_log" ) ? 1 : 0,
                        'field_order'        => sanitizer( "field_order", '0', 'field_order' ),
                        'field_section'      => $ref_module,
                    ];
                    $field_data['field_name'] = str_replace( ' ', '_', $field_data['field_name'] ); // make sure no space.
                    if ( !$field_data['field_order'] ) {
                        $field_data['field_order'] = dbresult( dbquery( "SELECT MAX(field_order) FROM ".DB_USER_FIELDS." WHERE field_cat=:cat_id", [ ':cat_id' => $field_data['field_cat'] ] ), 0 ) + 1;
                    }

                    $this->class->updateFields( $field_data, 'module', '', $this->modules );
                }
            }

            $field_data['add_module'] = $field_data['field_name'];

            if ( post( 'add_module' ) ) {
                $field_data['add_module'] = sanitizer( 'add_module', '', 'add_module' );
            }

            $html = openform( 'fieldform', 'post' );
            $html .= "<div><strong>".$plugin_data['user_field_name']."</strong></div>\n";
            if ( !empty( $plugin_data['user_field_desc'] ) ) {
                $html .= "<div class='spacer-xs m-t-0'>".$plugin_data['user_field_desc']."</div>";
            }
            $html .= "<div>\n";
            $html .= "<span class='m-b-10 strong'>".$locale['fields_0400']."</span><br/>\n<br/>\n";
            $html .= "<span class='strong'>".$locale['fields_0401']."</span> ".( $plugin_data['user_field_version'] ?: $locale['fields_0402'] )."<br/>\n";
            $html .= "<span class='strong'>".$locale['fields_0403']."</span> ".( $plugin_data['user_field_dbname'] ?: $locale['fields_0404'] )."<br/>\n";
            $html .= "<span class='strong'>".$locale['fields_0405']."</span> ".( $plugin_data['user_field_dbinfo'] ?: $locale['fields_0406'] )."<br/>\n";
            $html .= "</div>\n<hr/>\n";
            $html .= form_select( 'field_cat', $locale['fields_0410'], $field_data['field_cat'], [
                'no_root'      => TRUE,
                'db'           => DB_USER_FIELD_CATS,
                'disable_opts' => array_keys( $this->page_list ),
                'id_col'       => 'field_cat_id',
                'cat_col'      => 'field_parent',
                'title_col'    => 'field_cat_name'
            ] );
            $html .= form_text( 'field_order', $locale['fields_0414'], $field_data['field_order'], [ 'type' => 'number', 'inner_width' => '100px' ] );
            if ( !empty( $plugin_data['user_field_dbinfo'] ) ) {
                if ( version_compare( $plugin_data['user_field_version'], "1.01.00", ">=" ) ) {
                    $html .= form_checkbox( 'field_required', $locale['fields_0411'], $field_data['field_required'], [ 'reverse_label' => TRUE ] );
                    $html .= form_checkbox( 'field_log', $locale['fields_0412'], $field_data['field_log'], [ 'reverse_label' => TRUE ] );
                }
                $html .= form_checkbox( 'field_registration', $locale['fields_0413'], $field_data['field_registration'], [ 'reverse_label' => TRUE ] );
            }
            $html .= form_hidden( 'add_module', '', $field_data['add_module'] );
            $html .= form_hidden( 'field_name', '', $plugin_data['user_field_dbname'] );
            $html .= form_hidden( 'field_title', '', $plugin_data['user_field_name'] );
            // Where is this coming from?
            $html .= form_hidden( 'field_default', '', isset( $user_field_default ) ? $user_field_default : '' );
            $html .= form_hidden( 'field_options', '', isset( $user_field_options ) ? $user_field_options : '' );
            $html .= form_hidden( 'field_error', '', isset( $user_field_error ) ? $user_field_error : '' );
            $html .= form_hidden( 'field_config', '', isset( $user_field_config ) ? $user_field_config : '' );
            $html .= form_hidden( 'field_id', '', $field_data['field_id'] );
            $html .= "<hr/>\n";
            $html .= form_button( 'enable',
                ( $field_data['field_id'] ? $locale['fields_0415'] : $locale['fields_0416'] ),
                ( $field_data['field_id'] ? $locale['fields_0415'] : $locale['fields_0416'] ),
                [ 'class' => 'btn-default m-r-10' ] );
            $action_link = "<a href='".ADMIN."user_fields.php".$aidlink."&amp;ref=public&amp;action=new'>".$locale['cancel']."</a>";
            if ( $module_id ) {
                $action_link = "<a href='".ADMIN."user_fields.php".$aidlink."'>".$locale['cancel']."</a>";
            }
            $html .= $action_link;
            $html .= closeform();

            return (string)$html;

        }

        add_notice( 'danger', $locale['fields_0109'] );
        redirect( ADMIN.'user_fields.php'.$aidlink.'&amp;ref=public&amp;action=new' );

    }

}
