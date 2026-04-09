<?php
namespace PHPFusion\Infusions\Profile_Home\Classes;

/**
 * Class JournalForm
 * The modal form that is appended into the mainframe theme.
 *
 * @package PHPFusion\Infusions\Profile_Home\Classes
 *
 */
class JournalForm {
    
    private $data = [
        'journal_id'       => 0,
        'journal_cat'      => 0,
        'journal_sticky'   => 0,
        'journal_keywords' => '',
        'journal_text'     => '',
        'journal_subject'  => '',
    ];
    
    // we also need ajax save.
    public function __construct() {
        
        $this->showForm();
    }
    
    private $action = '';
    
    private function journalActions() {
        if ( defined( 'iPROFILE' ) ) {
            $this->action = get( 'action' );
            $journal_edit = get( 'journal', FILTER_VALIDATE_INT );
            if ( !$this->saveJournal() ) {
                
                // save journal here.
                if ( in_array( $this->action, [ 'edit', 'delete' ] ) && $journal_edit ) {
                    // only owner of the article can edit.
                    $result = dbquery( "SELECT * FROM ".DB_PROFILE_JOURNALS." WHERE journal_id=:jid AND journal_uid=:uid", [
                        ':uid' => fusion_get_userdata( "user_id" ),
                        ':jid' => $journal_edit
                    ] );
                    if ( dbrows( $result ) ) {
                        $this->data = dbarray( $result );
                        if ( !$this->updateJournal() && !$this->deleteJournal() ) {
                            return TRUE;
                        }
                    } else {
                        redirect( clean_request( '', [ 'action', 'journal' ], FALSE ) );
                    }
                }
            }
        }
        return TRUE;
    }
    
    /**
     * @param int    $journal_id
     * @param string $journal_subject
     *
     * @return bool
     */
    private function subjectCheck( int $journal_id = 0, string $journal_subject = '' ) {
        if ( $journal_id ) {
            return dbcount( "(journal_id)", DB_PROFILE_JOURNALS, "journal_subject=:subject AND journal_id !=:jid", [ ':jid' => $journal_id, ':subject' => $journal_subject ] );
        }
        return dbcount( "(journal_id)", DB_PROFILE_JOURNALS, "journal_subject=:subject", [ ':subject' => $journal_subject ] );
    }
    
    private function sanitizerInputFields() {
        $this->data['journal_cat'] = sanitizer( 'journal_cat', '', 'journal_cat' );
        $this->data['journal_sticky'] = sanitizer( 'journal_sticky', 0, 'journal_sticky' );
        $this->data['journal_keywords'] = sanitizer( 'journal_keywords', '', 'journal_keywords' );
        $this->data['journal_text'] = sanitizer( 'journal_text', '', 'journal_text' );
        $this->data['journal_subject'] = sanitizer( 'journal_subject', '', 'journal_subject' );
    }
    
    /**
     * @return bool     Error exists
     * @throws \Exception
     */
    private function saveJournal() {
        if ( post( 'save_journal' ) ) {
            $this->sanitizerInputFields();
            if ( fusion_safe() ) {
                if ( !$this->subjectCheck( 0, $this->data['journal_subject'] ) ) {
                    dbquery_insert( DB_PROFILE_JOURNALS, $this->data, 'save' );
                    add_notice( 'success', 'Journal '.$this->data['journal_subject'].' has been created' );
                    redirect( clean_request( '', [ 'action', 'journal' ], FALSE ) );
                }
                add_notice( 'danger', 'Journal subject already exist. Please try another subject name.' );
            }
            return TRUE;
        }
        return FALSE;
    }
    
    private function deleteJournal() {
        if ( $this->data['journal_id'] && $this->action == 'delete' ) {
            if ( dbcount( "(journal_id)", DB_PROFILE_JOURNALS, "journal_uid=:uid AND journal_id=:jid", [ ':uid' => fusion_get_userdata( 'user_id' ), ':jid' => $this->data['journal_id'] ] ) || ( iADMIN && checkrights( 'PFHP' ) ) ) {
                dbquery( "DELETE FROM ".DB_PROFILE_JOURNALS." WHERE journal_id=:jid", [ ':jid' => $this->data['journal_id'] ] );
                add_notice( 'success', 'Journal has been deleted', 'all' );
                redirect( clean_request( '', [ 'action', 'journal_id' ], FALSE ) );
            }
        }
        return FALSE;
    }
    
    
    public function updateJournal() {
        if ( post( 'save_journal' ) ) {
            $this->sanitizerInputFields();
            if ( fusion_safe() ) {
                if ( !$this->subjectCheck( $this->data['journal_id'], $this->data['journal_subject'] ) ) {
                    dbquery_insert( DB_PROFILE_JOURNALS, $this->data, 'update' );
                    add_notice( 'success', 'Journal '.$this->data['journal_subject'].' has been updated' );
                    redirect( clean_request( '', [ 'action', 'journal' ], FALSE ) );
                }
                add_notice( 'danger', 'Journal subject already exist. Please try another subject name.' );
            }
            return TRUE;
        }
        return FALSE;
    }
    
    
    public function showForm() {
        if ( iMEMBER && $this->journalActions() ) {
            
            add_to_footer( "<script src='".INFUSIONS."profile_home/templates/js/jcats.js'></script>" );
            
            $modal_config = [ 'class' => 'modal-lg' ];
            if ( !$this->data['journal_id'] ) {
                if (get('action') != 'new') {
                    $modal_config['button_class'] = 'submit-journal';
                }
            }
           
            $modal = openmodal( 'submit-journal-modal', '<strong>Submit Journal</strong>', $modal_config );
            $modal .= openform( 'submit_journal_form', 'post' );
            $modal .= form_hidden( 'uid', '', fusion_get_userdata( 'user_id' ) );
            $modal .= form_hidden( 'journal_id', '', $this->data['journal_id'] );
            $modal .= "<hr>";
            $modal .= "<div class='display-flex-row'>";
            $modal .= "<div class='display-inline-block'>";
            $modal .= form_select( 'journal_cat', 'Category', $this->data['journal_cat'], [
                'inline'       => TRUE,
                'db'           => DB_PROFILE_JOURNAL_CATS,
                'id_col'       => 'journal_cat_id',
                'cat_col'      => 'journal_cat_parent',
                'title_col'    => 'journal_cat_name',
                'select_alt'   => TRUE,
                'no_root'      => TRUE,
                'class'        => 'm-0',
                'custom_query' => "SELECT * FROM ".DB_PROFILE_JOURNAL_CATS." WHERE ".groupaccess( 'journal_cat_visibility' )." AND journal_cat_language='English' ORDER BY journal_cat_name ASC"
            ] );
            $modal .= "</div>";
            $modal .= "<div class='display-inline-block' style='margin-left:auto;'>";
            $modal .= "<div class='display-inline-block'>";
            $modal .= form_checkbox( 'journal_sticky', 'Featured Post', $this->data['journal_sticky'], [ 'reverse_label' => TRUE, 'class' => 'm-b-0 m-r-20' ] );
            $modal .= "</div>";
            $modal .= "<div class='display-inline-block'>";
            $modal .= form_button( 'save_draft', 'Save AS Draft', 'save_draft', [
                'class' => 'text-dark text-uppercase small strong btn-link m-l-20 m-r-20', 'deactivate' => TRUE ] );
            $modal .= form_button( 'save_journal', 'Submit Journal', 'save_journal', [
                'class' => 'text-uppercase small strong btn-success m-l-20 m-r-20', 'deactivate' => TRUE ] );
            $modal .= "</div>";
            $modal .= "</div>";
            $modal .= "</div>";
            $modal .= "<hr>";
            $modal .= '<div class="'.grid_container().'">';
            $modal .= form_text( 'journal_subject', '', $this->data['journal_subject'], [
                'required'    => TRUE, 'placeholder' => 'Add your title here...', 'class' => 'form-group-lg',
                'inner_class' => 'b-0' ] );
            $modal .= form_textarea( 'journal_text', '', $this->data['journal_text'], [
                'inner_class' => 'input-lg',
                'bbcode'      => FALSE,
                'form_name'   => 'submit_journal_form',
                'required'    => TRUE,
                'grippie'     => TRUE,
                
                'placeholder' => 'Type your main text here.', 'inner_class' => 'input-lg b-0' ] );
            $modal .= form_select( 'journal_keywords', '', $this->data['journal_keywords'], [
                'placeholder' => 'Add Keywords',
                'select_alt'  => TRUE,
                'tags'        => TRUE,
                'class'       => 'form-group-lg' ] );
            $modal .= '</div>';
            $modal .= closeform();
            $modal .= closemodal();
            
            add_to_footer( $modal );
        }
        
    }
    
}
