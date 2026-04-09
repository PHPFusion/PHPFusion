<?php
namespace PHPFusion\Infusions\Profile_Home\Classes;

use PHPFusion\Interfaces\TableSDK;
use PHPFusion\Tables;

class JournalCats {

    public function __construct() {
    }

    public function getCrumb() {
        return [
            'link'  => INFUSIONS.'profile_home/administration.php?'.fusion_get_aidlink().'&amp;ref=jcats',
            'title' => 'Journal Category Administration'
        ];
    }

    public function showAdmin() {
        echo '<h4>Journal Category Administration</h4><hr/>';
        echo '<div class="'.grid_row().'">';
        echo '<div class="'.grid_column_size( 100, 50, 50, 40 ).'">';
        echo $this->displayForm();
        echo '</div><div class="'.grid_column_size( 100, 50, 50, 60 ).'">';
        echo $this->displayList();
        echo '</div>';
    }

    private $data = [
        'journal_cat_id'         => 0,
        'journal_cat_name'       => '',
        'journal_cat_parent'     => '',
        'journal_cat_visibility' => USER_LEVEL_PUBLIC,
        'journal_cat_language'   => LANGUAGE,
        'journal_cat_draft'      => 0,
        'journal_cat_sticky'     => 0,
    ];


    private function validateCatUpdate() {
        if ( dbcount( "(journal_cat_id)", DB_PROFILE_JOURNAL_CATS, "journal_cat_id=:jid", [ ':jid' => (int)$this->data['journal_cat_id'] ] ) ) {
            if ( !dbcount( "(journal_cat_id)", DB_PROFILE_JOURNAL_CATS, "journal_cat_name=:name AND journal_cat_id !=:jid", [ ':name' => (string)$this->data['journal_cat_name'], ':jid' => (int)$this->data['journal_cat_id'] ] ) ) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private function validateCat() {
        if ( !dbcount( "(journal_cat_id)", DB_PROFILE_JOURNAL_CATS, "journal_cat_name=:name", [ ':name' => (string)$this->data['journal_cat_name'] ] ) ) {
            return TRUE;
        }
        return FALSE;
    }

    private function formAction() {
        if ( post( 'cancel' ) ) {
            redirect( clean_request( '', [ 'edit', 'del' ], FALSE ) );
        }

        if ( post( 'save_cat' ) ) {
            $this->data = [
                'journal_cat_id'         => sanitizer( 'journal_cat_id', '', 'journal_cat_id' ),
                'journal_cat_name'       => sanitizer( 'journal_cat_name', '', 'journal_cat_name' ),
                'journal_cat_parent'     => sanitizer( 'journal_cat_parent', '', 'journal_cat_parent' ),
                'journal_cat_visibility' => sanitizer( 'journal_cat_visibility', '', 'journal_cat_visibility' ),
                'journal_cat_language'   => sanitizer( 'journal_cat_language', '', 'journal_cat_language' ),
                'journal_cat_draft'      => sanitizer( 'journal_cat_draft', '', 'journal_cat_draft' ),
                'journal_cat_sticky'     => sanitizer( 'journal_cat_sticky', '', 'journal_cat_sticky' ),
            ];
            if ( fusion_safe() ) {
                if ( $this->data['journal_cat_id'] ) {
                    if ( $this->validateCatUpdate() ) {
                        add_notice( 'success', 'Journal Category Udpated' );
                        dbquery_insert( DB_PROFILE_JOURNAL_CATS, $this->data, 'update' );
                        return TRUE;
                    }
                }
                if ( $this->validateCat() ) {
                    add_notice( 'success', 'Journal Category Created' );
                    dbquery_insert( DB_PROFILE_JOURNAL_CATS, $this->data, 'save' );
                    return TRUE;
                }
                return FALSE;
            }
            return FALSE;
        }
    }

    private function formEdit() {
        $jid = get( 'edit', FILTER_VALIDATE_INT );
        if ( $jid ) {
            $result = dbquery( "SELECT * FROM ".DB_PROFILE_JOURNAL_CATS." WHERE journal_cat_id=:jid", [ ':jid' => (int)$jid ] );
            if ( dbrows( $result ) ) {
                $this->data = dbarray( $result );
                return TRUE;
            }
            return FALSE;
        }
        return TRUE;
    }

    private function displayForm() {

        if ( $this->formAction() or !$this->formEdit() ) {
            redirect( clean_request( '', [ 'edit', 'delete' ], FALSE ) );
        }

        // category setup
        echo openform( 'journalCatsFrm', 'post' ).
            form_hidden( 'journal_cat_id', '', $this->data['journal_cat_id'] ).
            form_text( 'journal_cat_name', 'Category Name', $this->data['journal_cat_name'], [ 'required' => TRUE ] ).
            form_select( 'journal_cat_parent', 'Category Parent', $this->data['journal_cat_parent'], [ 'select_alt' => TRUE, 'db' => DB_PROFILE_JOURNAL_CATS, 'id_col' => 'journal_cat_id', 'cat_col' => 'journal_cat_parent', 'title_col' => 'journal_cat_name' ] ).
            form_select( 'journal_cat_visibility', 'Category Visibility', $this->data['journal_cat_visibility'], [ 'select_alt' => TRUE, 'options' => fusion_get_groups() ] ).
            form_select( 'journal_cat_language', 'Category Language', $this->data['journal_cat_language'], [ 'select_alt' => TRUE, 'options' => fusion_get_enabled_languages() ] ).
            form_checkbox( 'journal_cat_draft', 'Save Category as Draft', $this->data['journal_cat_draft'], [ 'reverse_label' => TRUE ] ).
            form_checkbox( 'journal_cat_sticky', 'Featured Category', $this->data['journal_cat_sticky'], [ 'reverse_label' => TRUE ] ).
            '<hr>'.
            $this->displayFormButton().
            closeform();
    }

    private function displayList() {
        new Tables( new Journal_Table() );
    }

    private function displayFormButton() {
        if ( get( 'edit', FILTER_VALIDATE_INT ) ) {
            return form_button( 'save_cat', 'Update Category', 'save_cat', [ 'class' => 'btn-primary' ] ).form_button( 'cancel', 'Cancel', 'cancel' );
        }
        return form_button( 'save_cat', 'Save Category', 'save_cat', [ 'class' => 'btn-primary' ] );
    }

}

/**
 * Class Journal_Table
 *
 * @package PHPFusion\Infusions\Profile_Home\Classes
 */
class Journal_Table implements TableSDK {

    public function data() {
        return [
            'table'  => DB_PROFILE_JOURNAL_CATS,
            'id'     => 'journal_cat_id',
            'title'  => 'journal_cat_name',
            'parent' => 'journal_cat_parent',
            'limit'  => 16,
        ];
    }

    public function properties() {
        return [
            'search_col'    => [ 'journal_cat_id', 'journal_cat_name' ],
            'table_id'      => 'journalTable',
            'order_col'     => [
                'journal_cat_name'       => 'title',
                'journal_cat_language'   => 'language',
                'journal_cat_draft'      => 'draft',
                'journal_cat_sticky'     => 'sticky',
                'journal_cat_visibility' => 'visibility',
            ],
            'multilang_col' => 'journal_cat_language'
        ];
    }

    public function column() {
        return [
            'journal_cat_name'       => [ 'title' => 'Category', 'edit_link' => TRUE, 'delete_link' => TRUE, ],
            'journal_cat_visibility' => [ 'title' => 'Access', 'callback' => 'callbackGroup' ],
            'journal_cat_language'   => [ 'title' => 'Language' ],
            'journal_cat_draft'      => [ 'title' => 'Draft', 'options' => [ 1 => 'Draft', 0 => '' ] ],
            'journal_cat_sticky'     => [ 'title' => 'Sticky', 'options' => [ 1 => 'Sticky', 0 => '' ] ],
        ];
    }

    public function quickEdit() {
        // TODO: Implement quickEdit() method.
    }
}

