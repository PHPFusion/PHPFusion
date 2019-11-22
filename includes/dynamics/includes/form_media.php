<?php
/**
 * A file selector and uploader that triggers a modal output for file selection
 * Support configurable paths to access folders
 *
 * @param string $input_name
 * @param string $input_label
 * @param string $input_value
 * @param array  $options
 *
 * @return string
 */

/**
 * @param string $input_name
 * @param string $input_label
 * @param string $input_value
 * @param array  $options
 *
 * @return string
 * @throws ReflectionException
 */
class FusionMedia {
    
    private $input_name;
    private $input_label;
    private $input_value;
    private $options = [];
    
    public function __construct( string $input_name, string $input_label = '', string $input_value = '', array $options = [] ) {
        if ( !defined( 'form_media' ) ) {
            define( 'form_media', TRUE );
            add_to_head( "<link rel='stylesheet' href='".DYNAMICS."assets/fusionmedia/fusionmedia.css'/>" );
        }
        
        $this->input_name = $input_name;
        $this->input_label = $input_label;
        $this->input_value = $input_value;
        
        $default_options = [
            'title'            => 'Media Gallery',
            'select_btn_text'  => 'Select',
            'select_btn_class' => 'btn-success',
            'select_btn_icon'  => '',
            'class'            => 'btn-default',
            'icon'             => '',
            'folders'          => [],
        ];
        
        $this->options = $options + $default_options;
        
        return $this;
    }
    
    public function view() {
        
        $modal = openmodal( 'media-gll-'.$this->input_name, $this->options['title'], [ 'class' => 'modal-md fs-media' ] );
        $modal .= '<div class="media-nav">';
        $modal .= form_button( 'upload_media', 'Upload', 'upload_media', [ 'class' => 'btn-default btn-upload text-uppercase small btn-block' ] );
        if ( !empty( $this->options['folders'] ) ) {
            $modal .= '<ul class="nav nav-pills nav-stacked" role="tablist">';
            $content = '';
            $count = 0;
            foreach ( $this->options['folders'] as $folder ) {
                $default_folder = [
                    'title' => '',
                    'icon'  => '',
                    'file'  => '',
                    'path'  => ''
                ];
                $folder += $default_folder;
                
                $tab['id'][] = $folder['id'];
                $tab['title'][] = $folder['title'];
                $tab['icon'][] = $folder['icon'];
                
                $id = $this->input_name.'_c_'.$folder['id'];
                
                $active_class = ( !$count ? ' class="active"' : '' );
                $active_class2 = ( !$count ? ' active' : '' );
                
                $modal .= '<li role="presentation"'.$active_class.'><a href="#'.$id.'" aria-controls="profile" role="tab" data-toggle="tab">'.$folder['title'].'</a></li>';
                
                // Render Tab Content
                $content .= '<div role="tabpanel" class="tab-pane'.$active_class2.'" id="'.$id.'">'.$this->loadMediaPage( $folder['file'] ).'</div>';
                
                $count++;
            }
            $modal .= '</ul>';
        }
        // directory structure.
        $modal .= '</div><div class="media-content">';
        // gallery
        $modal .= '<div class="tab-content">';
        $modal .= ( isset( $content ) ? $content : '<h4>There are no folders defined</h4>' );
        $modal .= '</div>';
        $modal .= '</div>';
        
        $modal .= modalfooter( form_button( $this->input_name.'_sel', $this->options['select_btn_text'], $this->input_name.'_sel', [ 'class' => $this->options['select_btn_class'], 'icon' => $this->options['select_btn_icon'] ] ), FALSE );
        $modal .= closemodal();
        
        add_to_footer( $modal );
        
        return form_button( $this->input_name, $this->input_label, $this->input_name, [ 'class' => $this->options['class'], 'icon' => $this->options['icon'] ] );
    }
    
    private function loadMediaPage( $file ) {
        if ( is_file( $file ) ) {
            ob_start();
            include $file;
            return ob_get_clean();
        }
        return '<h4>The current file could not be loaded</h4>';
    }
    
}

/**
 * Fusion Media Gallery
 *
 * @param string $input_name
 * @param string $input_label
 * @param string $input_value
 * @param array  $options
 */
function fusion_media( string $input_name, string $input_label = '', string $input_value = '', array $options = [] ) {
    $media = new FusionMedia( $input_name, $input_label, $input_value, $options );
    echo $media->view();
}
