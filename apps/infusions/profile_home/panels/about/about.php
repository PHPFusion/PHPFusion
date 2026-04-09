<?php

use PHPFusion\Template;
use PHPFusion\UserFieldsQuantum;

class AboutPanel extends UserFieldsQuantum {

    private $data;

    public function __construct( $data ) {
        $this->data = $data;
        // install all the necessary fields
    }

    private function editModal() {
        $locale = fusion_get_locale();

        $result = dbquery( "SELECT * FROM ".DB_USER_FIELD_CATS." WHERE field_parent=1 ORDER BY field_cat_order ASC" );
        if ( dbrows( $result ) ) {

            // update profile mechanism
            $userFieldsInput = new UserFieldsInput();
            $userFieldsInput->user_data = $this->data;
            $userFieldsInput->saveUpdate();

            $field_options = [];
            $field_result = dbquery( "SELECT * FROM ".DB_USER_FIELDS." WHERE field_section='public' ORDER BY field_cat ASC, field_order ASC" );
            if ( dbrows( $field_result ) ) {
                while ( $fields = dbarray( $field_result ) ) {
                    $field_options[ $fields['field_cat'] ][ $fields['field_id'] ] = $fields;
                }
            }

            while ( $data = dbarray( $result ) ) {

                // do the tab
                $tab['id'][] = 'fieldcat-'.$data['field_cat_id'];
                $tab['title'][] = fusion_parse_locale( $data['field_cat_name'] );
                $tab['fields'][] = ( !empty( $field_options[ $data['field_cat_id'] ] ) ? $field_options[ $data['field_cat_id'] ] : [] );

            }
        }
        if ( !empty( $tab ) ) {

            $this->loadUserFields( 'public' );

            $this->cacheModulePlugins();

            $modal = openmodal( 'editAbout', '<strong>My Info</strong>', [ 'hidden' => TRUE, 'class' => 'modal-md' ] );
            $modal .= openform( 'userProfileFrm', 'post' );

            $tab_active = tab_active( $tab, 0 );

            $modal .= opentab( $tab, $tab_active, 'ufc-tabs', FALSE, 'nav-pills nav-sm m-b-20' );

            foreach ( $tab['id'] as $index => $tab_id ) {

                $modal .= opentabbody( $tab['title'][ $index ], $tab_id, $tab_active );

                // display all fields.
                $fields = $tab['fields'][ $index ];
                if ( !empty( $fields ) ) {
                    $count = 0;
                    $modal .= "<div class='display-flex-row space-between'>";
                    foreach ( $fields as $field_index => $field_data ) {
                        // forced width
                        $options = [
                            'width'       => '100%',
                            'inner_width' => '100%',
                        ];
                        if ( !$index ) {
                            $options = [ 'width' => '200px', 'inner_width' => '200px' ];
                        }

                        if ( ( $count == 3 && !$index ) or ( $count == 1 && $index ) ) {
                            $modal .= "</div><div class='display-flex-row space-between'>";
                            $count = 0;
                        }
                        // we need to know which file is needed

                        $modal .= $this->displayFields( $field_data, $this->data, 'input', [ 'select_alt' => TRUE, 'inline' => FALSE ] + $options );

                        $count++;
                    }
                    $modal .= "</div>";
                }

                $modal .= closetabbody();
            }

            $modal .= closetab();
            $modal .= modalfooter( form_button( 'update_profile', $locale['save'], 'update_profile', [ 'class' => 'btn-success' ] ), TRUE );
            $modal .= closeform();
            $modal .= closemodal();
            add_to_footer( $modal );

            add_to_jquery( "
            $('a[data-toggle=\"edit\"][data-value=\"about\"]').bind('click', function(e) {
                e.preventDefault();
                // trigger modal
                $('#editAbout-Modal').modal('show');
            });
            " );
        }
    }

    public function viewPanel() {
        if ( iPROFILE || iADMIN && checkrights( 'M' ) ) {
            $this->editModal();
        }


        $tpl = Template::getInstance( 'about-panel' );
        $tpl->set_template( __DIR__.'/about.html' );
        // edit lines
        $tpl->set_tag( 'edit_id', 'toggle-about-edit' );
        // user joined
        $tpl->set_tag( 'user_tagline', $this->getTagLine() );
        $tpl->set_tag( 'user_birthdate', timer( $this->data['user_joined'] ) );
        $tpl->set_tag( 'user_joined', timer( $this->data['user_joined'] ) );
        $tpl->set_tag( 'user_location', $this->data['user_location'] );
        $tpl->set_tag( 'user_bio', nl2br( $this->data['user_bio'] ) );
        // user web
        $tpl->set_tag( 'user_web', $this->data['user_web'] );
        $tpl->set_tag( 'user_gender', $this->getGender() );
        if ( !empty( $this->data['user_tools'] ) ) {
            $tpl->set_block( 'bio_block', [
                'block_title' => 'Frequently used Tools',
                'block_value' => $this->data['user_tools']
            ] );
        }

        return $tpl->get_output();
    }


    private function getTagLine() {
        $html_arr = [];
        if ( !empty( $this->data['user_engage'] ) ) {
            $html_arr[] = $this->data['user_engage'];
        }
        if ( !empty( $this->data['user_specialize'] ) ) {
            $html_arr[] = $this->data['user_specialize'];
        }
        if ( !empty( $this->data['user_role'] ) ) {
            $html_arr[] = $this->data['user_role'];
        }
        return implode( ' | ', $html_arr );
    }


    private function getGender() {
        if ( $this->data['user_gender'] == 2 ) {
            return 'Others';
        }
        if ( $this->data['user_gender'] == 1 ) {
            return 'She/Her';
        }
        return 'He/Him';
    }

}
