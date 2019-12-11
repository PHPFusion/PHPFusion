<?php
namespace PHPFusion\Infusions\Blog\Classes;

use PHPFusion\Interfaces\TableSDK;

class BlogList implements TableSDK {
    
    /**
     *  Returns the table data source structure configurations
     *
     *
     * 'debug'                    => FALSE, // True to show the SQL query for the table.
     * 'table'                    => '', // db name
     * 'id'                       => '', // id column name
     * 'title'                    => '', // title column name
     * 'parent'                   => '', // parent column name (if hierarchy)
     * 'limit'                    => 24, // limit
     * 'true_limit'               => FALSE, // if true, the limit is true limit (only limited results will display without page nav)
     * 'joins'                    => '', // INNER JOIN etc on etc=base.id
     * 'select'                   => '', // addditional
     * 'conditions'               => '', // to match list to a condition. string value only
     * 'group'                    => '', // group by column
     * 'image_folder'             => '', // for deletion (i.e. IMAGES.'folder/') , use param for string match
     * 'image_field'              => '', // to delete (i.e. news_image)
     * 'file_field'               => '',  // to delete (i.e. news_attach)
     * 'file_folder'              => '', // to delete files from the folder, use param for string match
     * 'db'                       => [], // to delete other entries on delete -- use this key. Keys: 'select' => 'ratings_id', 'group' => 'ratings_item_id', 'custom' => "rating_type='CLS'"
     * 'delete_function_callback' => '',
     *
     * @return array
     */
    public function data() {
        // TODO: Implement data() method.
        return [
            'table'                    => DB_BLOG,
            'id'                       => 'blog_id',
            'title'                    => 'blog_subject',
            'delete_function_callback' => [ 'BlogFormActions', 'deleteBlog' ]
        ];
    }
    
    public function properties() {
        
        //https://php-fusion.test/infusions/blog/blog_admin.php?aid=d95bf831949d46b7&action=edit&section=blog_form&blog_id=3
        return [
            'table_id'           => 'blog_list_admin',
            'multilang_col'      => 'blog_language',
            'search_label'       => 'Search Blog',
            'search_col'         => [ 'blog_subject, blog_id' ],
            'dropdown_filters'   => [
                'blog_cat' => [
                    'type'    => 'array',
                    'title'   => 'Blog Category',
                    'options' => $this->getBlogCat()
                ]
            ],
            'edit_link_format'   => INFUSIONS.'blog/blog_admin.php'.fusion_get_aidlink().'&amp;section=blog_form&amp;action=edit&amp;blog_id=',
            'delete_link_format' => INFUSIONS.'blog/blog_admin.php'.fusion_get_aidlink().'&amp;section=blog_form&amp;action=delete&amp;blog_id=',
            'view_link_format'   => INFUSIONS.'blog/blog.php&amp;readmore=',
        ];
    }
    
    /**
     * Returns the table outlook/presentation configurations
     *
     * 'table_class'        => '',
     * 'header_content'     => '',
     * 'no_record'          => 'There are no records',
     * 'search_label'       => 'Search',
     * 'search_placeholder' => "Search",
     * 'search_col'         => '', // set this value sql column name to have search input input filter
     * 'delete_link' => TRUE,
     * 'edit_link' => TRUE,
     * 'edit_link_format'   => '', // set this to format the edit link
     * 'delete_link_format' => '', // set this to format the delete link
     * 'view_link_format' => '', // set this to format the view link
     *
     * 'edit_key'           => 'edit',
     * 'del_key'            => 'del', // change this to invoke internal table delete function for custom delete link format
     * 'view_key'           => 'view',
     *
     * 'date_col'           => '',  // set this value to sql column name to have date selector input filter
     * 'order_col'          => '', // set this value to sql column name to have sorting column input filter
     * 'multilang_col'      => '', // set this value to have multilanguage column filter
     * 'updated_message'    => 'Entries have been updated', // set this value to have custom success message
     * 'deleted_message'    => 'Entries have been deleted', // set this value to have the custom delete message,
     * 'class'              => '', // table class
     * 'show_count'         => TRUE // show table item count,
     * // This will add an extra link on top of the bulk actions selector
     * 'link_filters'       => [
     * 'group_key' => [
     *                  [$key_values => $key_title],
     *                  [$key_values => $key_title]
     *              ]
     * ]
     * // This will add extra dropdown pair of dropdown selectors to act as column filter that has such value.
     * 'dropdown_filters' => [
     *          'user_level' => [
     *          'type' => 'array', // use 'date' if the column is a datestamp
     *          'title' => $title',
     *          'options' => [ [$key_values => $key_title], [$key_values => $key_title], ... ] ] //$key_values - This is the key to be used on actions_filters_confirm
     *          ]
     * ],
     * // This will add your confirmation messages -- key_values is the key to 'dropdown_filters'['options'][key']
     * 'actions_filters_confirm' => [
     * 'key_values' => 'Are you sure to delete this record?'
     * ],
     *  // This allows you to add more options to the bulk filters.
     * 'action_filters'   => [
     * 'text'     => 'Member Actions',
     * 'label'    => TRUE,
     * 'children' => [
     * Members::USER_BAN          => $locale['ME_500'],
     * Members::USER_REINSTATE    => $locale['ME_501'],
     * Members::USER_SUSPEND      => $locale['ME_503'],
     * Members::USER_SECURITY_BAN => $locale['ME_504'],
     * Members::USER_CANCEL       => $locale['ME_505'],
     * Members::USER_ANON         => $locale['ME_506'],
     * Members::USER_DEACTIVATE   => $locale['ME_507']
     * ]
     * ]
     *
     *
     *
     * @return array
     */
    public function getBlogCat() {
        $result = dbquery( "SELECT blog_cat_id, blog_cat_name FROM ".DB_BLOG_CATS." ".( multilang_table( "BL" ) ? "WHERE ".in_group( 'blog_cat_language', LANGUAGE ) : "" )." ORDER BY blog_cat_name ASC" );
        $cat_options = [];
        if ( dbrows( $result ) ) {
            while ( $data = dbarray( $result ) ) {
                $cat_options[ $data['blog_cat_id'] ] = $data['blog_cat_name'];
            }
        }
        return $cat_options;
    }
    
    /**
     * Returns the column structure configurations
     *
     * 'title'         => '',
     * 'title_class'   => '',
     * 'value_class'   => '',
     * 'edit_link'     => FALSE,
     * 'delete_link'   => FALSE,
     * 'image'         => FALSE,
     * 'image_folder'  => '', // set image folder (method2)
     * 'default_image' => '',
     * 'image_width'   => '', // set image width
     * 'image_class'   => '', // set image class
     * 'icon'          => '',
     * 'empty_value'   => '',
     * 'count'         => [],
     * 'view_link'     => '',
     * 'display'       => [], // API for display
     * 'date'          => FALSE,
     * 'options'       => [],
     * 'user'          => FALSE,
     * 'user_avatar'   => FALSE, // show avatar
     * 'number'        => FALSE,
     * 'format'        => FALSE, // for formatting using strtr
     * 'callback'      => '', // for formatting using function
     * 'debug'         => FALSE,
     * 'visibility'    => FALSE, // set this column to hide by default until user enables it via custom
     *
     * @return array
     */
    public function column() {
        return [
            'blog_subject'        => [
                'title'       => 'Blog Subject',
                'title_class' => 'col-xs-4',
                'view_link'   => TRUE,
                'delete_link' => TRUE,
                'edit_link'   => TRUE,
            ],
            //'blog_image_t1'       => [
            //    'title'  => 'Blog Image',
            //    //@todo: can use callback for the full image path
            //    'format' => thumbnail( IMAGES_B_T.':blog_image_t1', '50px' ),
            //],
            'blog_cat'            => [
                'title'   => 'Blog Category',
                'options' => $this->getBlogCat()
            ],
            'blog_name'           => [
                'title' => 'Author',
                'user'  => TRUE,
            ],
            'blog_datestamp'      => [
                'title'       => 'Last updated',
                'date'        => TRUE,
                'date_format' => 'newsdate',
            ],
            'blog_start'          => [
                'title'       => 'Start Date',
                'date'        => TRUE,
                'date_format' => 'newsdate'
            ],
            'blog_end'            => [
                'title'       => 'End Date',
                'date'        => TRUE,
                'date_format' => 'newsdate'
            ],
            'blog_reads'          => [
                'title'     => 'Reads',
                'number'    => TRUE,
                'delimiter' => 0,
            ],
            'blog_draft'          => [
                'title'   => 'Draft',
                'options' => [ 0 => 'Published', 1 => 'Draft' ]
            ],
            'blog_sticky'         => [
                'title'   => 'Sticky',
                'options' => [ 0 => 'No', 1 => 'Yes' ]
            ],
            'blog_allow_comments' => [
                'title'   => 'Allow Comments?',
                'options' => [ 0 => 'No', 1 => 'Yes' ]
            ]
        ];
        
    }
    
    /**
     * Function to progressively return closest full image_path
     *
     * @param      $blog_image
     * @param      $blog_image_t1
     * @param      $blog_image_t2
     * @param bool $hiRes
     *
     * @return string
     */
    function getBlogImage( $blog_image, $blog_image_t1, $blog_image_t2, $hiRes = FALSE ) {
        return Functions::get_blog_image_path( $blog_image, $blog_image_t1, $blog_image_t2, $hiRes );
    }
    
    /**
     * Every row of the array is a field input.
     *
     * @return array
     */
    public function quickEdit() {
        return [];
    }
}

class BlogFormActions {
    
    public function deleteBlog() {
    
    }
    
}
