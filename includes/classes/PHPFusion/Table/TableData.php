<?php
namespace PHPFusion\Table;

use PHPFusion\Tables;

/**
 * Class TableData
 *
 * @package PHPFusion\Table
 */
class TableData {
    
    private $table_class = NULL;
    
    public function __construct( Tables $table_class ) {
        $this->table_class = $table_class;
    }
    
    /**
     * array("image"=>TRUE);
     *
     * @param $col_key
     * @param $data
     * @param $col_data
     * @param $data_replace
     *
     * @return string
     */
    public function imageColumn( $col_key, $data, $col_data, $data_replace ) {
        
        $width = ( !empty( $col_data['image_width'] ) ? $col_data['image_width']."; max-width: ".$col_data['image_width'] : 'max-width:150px;' );
        
        $class = ( !empty( $col_data['image_class'] ) ? $col_data['image_class'] : 'img-responsive' );
        
        $image_folder = '';
        if ( !empty( $this->table_class->query['image_folder'] ) ) {
            $image_folder = strtr( $this->table_class->query['image_folder'], $data_replace );
            
        } else if ( !empty( $col_data['image_folder'] ) ) {
            $image_folder = strtr( $col_data['image_folder'], $data_replace );
        }
        
        $default_image_path = ( $col_data['default_image'] ?: IMAGES.'imagenotfound.jpg' );
        
        $value = "<img class='$class' style='$width' src='$default_image_path'/>\n";;
        
        if ( is_file( $image_folder.$data[ $col_key ] ) ) {
            $value = "<img class='$class' style='$width' src='".$image_folder.$data[ $col_key ]."'/>\n";
        }
        return $value;
    }
    
    /**
     * array("icon"=>TRUE);
     *
     * @param $data
     * @param $col_key
     *
     * @return string
     */
    public function iconColumn( $col_key, $data ) {
        return "<i class='".$data[ $col_key ]."'/></i>\n";
    }
    
    /**
     * array("number" => true, "delimiter" => "2", "decimal_point" => ".", "thousand_sep" => ",")
     *
     * @param $value
     * @param $col_key
     * @param $data
     * @param $col_data
     *
     * @return string
     */
    public function numberColumn( $value, $col_key, $data, $col_data ) {
        
        if ( !empty( $data[ $col_key ] ) && isnum( $data[ $col_key ] ) ) {
            $value = number_format(
                $data[ $col_key ],
                ( !empty( $col_data['delimiter'] ) ? $col_data['delimiter'] : 0 ),
                ( !empty( $col_data['decimal_point'] ) ? $col_data['decimal_point'] : '.' ),
                ( !empty( $col_data['thousand_sep'] ) ? $col_data['thousand_sep'] : ',' )
            );
            
            return $value;
            
        } else {
            
            $value = number_format(
                $value ?: '0',
                ( !empty( $col_data['delimiter'] ) ? $col_data['delimiter'] : 0 ),
                ( !empty( $col_data['decimal_point'] ) ? $col_data['decimal_point'] : '.' ),
                ( !empty( $col_data['thousand_sep'] ) ? $col_data['thousand_sep'] : ',' )
            );
            
            if ( empty( $value ) ) {
                $value = ( !empty( $col_data['delimiter'] ) ? "0".( !empty( $col_data['thousand_sep'] ) ? $col_data['thousand_sep'] : ',' ).str_repeat(
                        $col_data['delimiter'], "0"
                    ) : "0" );
                //print_P($col_key.' '.$value);
                
            }
            
            return $value;
            
            
        }
    }
    
    public function optionsColumn( $value, $col_data ) {
        $option = $col_data['options'];
        if ( isset( $option[ $value ] ) ) {
            $value = $option[ $value ];
        }
        return $value;
    }
    
    /***
     * array("count" => array("field"=>"column_name", "table" => DB_PREFIX, "conditions" => "column_name=matches"))
     *
     * @param $value
     * @param $data
     * @param $col_data
     *
     * @return bool
     */
    public function countColumn( $value, $data, $col_data ) {
        
        if ( !empty( $col_data['count']['field'] ) && !empty( $col_data['count']['table'] ) && !empty( $col_data['count']['conditions'] ) ) {
            
            $data_replace = [];
            
            foreach ( array_keys( $data ) as $keyname ) {
                $data_replace[ ':'.$keyname ] = $data[ $keyname ];
            }
            
            $col_data['count']['conditions'] = strtr( $col_data['count']['conditions'], $data_replace );
            
            $value = dbcount( "(".$col_data['count']['field'].")", $col_data['count']['table'], $col_data['count']['conditions'] );
        }
        return $value;
    }
    
    /**
     * array("user" => TRUE);
     *
     * @param $value
     * @param $col_data
     *
     * @return string
     */
    public function userColumn( $value, $col_data ) {
        
        $user = fusion_get_user( $value );
        if ( !empty( $user['user_id'] ) ) {
            $avatar = '';
            if ( $col_data['user_avatar'] === TRUE ) {
                $avatar = display_avatar( $user, '32px', 'm-r-10' );
            }
            $value = $avatar.profile_link( $user['user_id'], $user['user_name'], $user['user_status'] );
        }
        
        return $value;
        
    }
    
    /**
     * array('display' => array(
     * 'key'        => 'feature_id',
     * 'table'      => DB_CLASS_FEATURE,
     * 'conditions' => "feature_id IN (:cat_feature)",
     * 'title'      => 'feature_title',
     * 'format_result' => "<a href='".INFUSIONS."farlayne/administration/listings/features.php?".fusion_get_aidlink()."&amp;edit=:key'>:title</a>",
     * )
     * )
     *
     * @param $value
     * @param $data
     * @param $col_key
     * @param $col_data
     * @param $data_replace
     *
     * @return array|mixed|string
     */
    public function displayColumn( $value, $data, $col_key, $col_data, $data_replace ) {
        
        if ( !empty( $data[ $col_key ] ) ) {
            
            $default_select = '';
            $default_condition = '';
            $default_order = " ORDER BY ".$col_data['display']['key']." ASC";
            
            if ( !empty( $col_data['display']['select'] ) ) {
                $col_data['display']['select'] = ", ".$col_data['display']['select'];
            } else {
                $col_data['display']['select'] = $default_select;
            }
            
            if ( !empty( $col_data['display']['conditions'] ) ) {
                if ( $col_data['debug'] === TRUE ) {
                    print_p( $data_replace );
                    print_p( $col_data['display']['conditions'] );
                }
                $col_data['display']['conditions'] = " WHERE ".strtr(
                        $col_data['display']['conditions'], $data_replace
                    );
            } else {
                $col_data['display']['conditions'] = $default_condition;
            }
            
            if ( empty( $col_data['display']['order'] ) ) {
                $col_data['display']['order'] = $default_order;
            }
            
            $formatted_value = [];
            $sub_query = "SELECT ".$col_data['display']['key'].", ".$col_data['display']['title'].$col_data['display']['select']." FROM ".$col_data['display']['table']." ".$col_data['display']['conditions']." ".$col_data['display']['order']." ".( !empty( $col_data['display']['limit'] ) ? $col_data['display']['limit'] : '' );
            if ( $col_data['debug'] === TRUE ) {
                print_p( $sub_query );
                print_p( $data_replace );
            }
            
            $sub_query = dbquery( $sub_query );
            
            if ( dbrows( $sub_query ) ) {
                
                while ( $sub_data = dbarray( $sub_query ) ) {
                    
                    $search_str = [
                        ':key'   => $sub_data[ $col_data['display']['key'] ],
                        ':title' => $sub_data[ $col_data['display']['title'] ]
                    ];
                    
                    //print_p($sub_data);
                    foreach ( $data as $key => $value ) {
                        $search_str[ ":".$key ] = $value;
                    }
                    
                    // we need sub_data to support all previous columns
                    $sub_data = array_merge( $sub_data, $data );
                    
                    if ( $col_data['debug'] === TRUE ) {
                        print_p( $search_str );
                        print_P( $sub_data );
                        print_p( $col_data['display']['format_result'] );
                    }
                    $formatted_value[] = ( !empty( $col_data['display']['format_result'] ) ? strtr(
                        urldecode( $col_data['display']['format_result'] ), $search_str
                    ) : $sub_data[ $col_data['display']['title'] ] );
                }
                
                $formatted_value = array_filter( $formatted_value );
                
                if ( $col_data['debug'] === TRUE ) {
                    print_p( $formatted_value );
                }
            } else {
                // No result
                $search_str = [];
                foreach ( $data as $key => $value ) {
                    $search_str[ ":".$key ] = $value;
                }
                $formatted_value = !empty( $col_data['display']['no_result'] ) ? strtr(
                    urldecode( $col_data['display']['no_result'] ), $search_str
                ) : "";
            }
            
            if ( !empty( $formatted_value ) ) {
                if ( is_array( $formatted_value ) ) {
                    $value = implode( ', ', $formatted_value );
                } else {
                    $value = $formatted_value;
                }
            }
            
            return $value;
        }
        return $value;
    }
    
    /**
     * array("date" => TRUE, "date_format" => "shortdate")
     *
     * @param $value
     * @param $col_data
     *
     * @return string|null
     */
    public function dateColumn( $value, $col_data ) {
        // only accepts timestamp
        if ( isnum( $value ) ) {
            // if less than a day,
            if ( time() > $value && ( ( time() - $value ) <= 86400 ) ) {
                return timer( $value );
            }
            if ( !empty( $col_data['date_format'] ) ) {
                return showdate( $col_data['date_format'], $value );
            }
            return showdate( 'shortdate', $value );
        }
        
        return $value;
        
    }
    
    /**
     * array("array" => array(1=>"Yes", 0=>"No"))
     *
     * @param $value
     * @param $col_data
     *
     * @return mixed
     */
    public function arrayColumn( $value, $col_data ) {
        return isset( $col_data['array'][ $value ] ) ? $col_data['array'][ $value ] : $col_data['array'][0];
    }
    
    /**
     * Formats the value into a specific format
     * array("format" => ":column_name item(s)")
     * array("format"      => "<a href='".ROADMAP."?id=:item_id'>:item_summary</a>")
     *
     * @param $value
     * @param $col_key
     * @param $col_data
     * @param $data_replace
     *
     * @return string
     */
    public function formatColumn( $value, $col_key, $col_data, $data_replace ) {
        
        if ( $col_data['debug'] ) {
            print_p( $data_replace );
            print_p( $value );
        }
        
        if ( !empty( $value ) ) {
            $data_replace[ ':'.$col_key ] = $value;
        }
        
        return strtr( $col_data['format'], $data_replace );
    }
    
    /**
     * Run your own callback function with the current column data
     * Usage on Class Callback: $options['callback'] = array("object_class", "custom_function", "the-path-to-your-class");
     * Usage on Function Callback: $options['callback'] = "custom_function";
     *
     * @param $value
     * @param $col_key
     * @param $col_data
     * @param $data_replace
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function callbackColumn( $value, $col_key, $col_data, $data_replace ) {
        
        if ( !empty( $value ) ) {
            $data_replace[ ':'.$col_key ] = $value;
        }
        
        if ( is_array( $col_data['callback'] ) ) {
            // head to new reflexion class
            // for reflection to work you need to push in your PSR4 autoloader file.
            if ( isset( $col_data['callback'][2] ) ) {
                require_once $col_data['callback'][2];
                unset( $col_data['callback'][2] );
            }
            //print_p(get_called_class());
            $object = new \ReflectionClass( $col_data['callback'][0] );
            $class = $object->newInstance();
            $method = [
                $class,
                $col_data['callback'][1]
            ];
            if ( is_callable( $method ) ) {
                $value = call_user_func( $method, $data_replace );
            } else if ( $col_data['debug'] ) {
                addNotice( "danger", "Callback could not be made" );
            }
        } else {
            //print_p(get_defined_functions());
            // If your file has a namespace, please add them in as prefix
            // some_namespace/callback_function_name
            if ( is_callable( $col_data['callback'] ) ) {
                $value = $col_data['callback']( $data_replace );
            } else if ( $col_data['debug'] ) {
                addNotice( "danger", "Callback could not be made" );
            }
        }
        
        return $value;
    }
    
}
