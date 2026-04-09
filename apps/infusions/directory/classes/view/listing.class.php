<?php
namespace PHPFusion\Infusions\Directory\Classes\View;

/**
 * Add Listing Form
 */
class Listing {
    
    public function view() {
        $locale = fusion_get_locale();
        add_to_meta("description", "Add a listing to Firstcamp");
        add_to_title("Add a Listing");
        
        $info = $this->baseInfo();
        
        if (in_array(get('t'), ['place', 'event', 'course'])) {
            
            if ($pack = get('v', FILTER_VALIDATE_INT)) {
                session_add('package-list', $pack);
                redirect(BASEDIR.'add-listing.php?t=place');
            }
            
            if (in_array(session_get('package-list'), [1, 2, 3, 4])) {
                
                $info = $this->formInfo();
                
            } else {
                
                $info = $this->packageInfo();
            }
        } else {
            session_remove('package-list');
        }
        
        return fusion_render(INFUSIONS.'directory/templates/', 'listing.twig', $info, TRUE);
    }
    
    private function baseInfo() {
        return [
            'item_type' => [
                [
                    'icon'  => 'far fa-map-marker-alt',
                    'class' => 'location',
                    'title' => 'Place',
                    'link'  => BASEDIR.'add-listing.php?t=place'
                ],
                [
                    'icon'  => 'far fa-music-alt',
                    'class' => 'event',
                    'title' => 'Event',
                    'link'  => BASEDIR.'add-listing.php?t=event'
                ],
                [
                    'icon'  => 'far fa-diploma',
                    'class' => 'course',
                    'title' => 'Course',
                    'link'  => BASEDIR.'add-listing.php?t=course'
                ],
            ]
        ];
    }
    
    private function formInfo() {
        
        switch (get('t')) {
            case 'place':
                $form_info = $this->placeFormInfo();
                break;
            default:
                $form_info = [];
        }
        
        return [
            'form'      => $form_info,
            'openform'  => openform('pcfrm', 'POST', FORM_REQUEST, ['autocomplete_off' => TRUE]),
            'closeform' => closeform()
        ];
    }
    
    private function placeFormInfo() {
        
        require_once CLASSES.'Mapbox/mapbox_include.php';
        
        //print_p($_POST);
        $data = [
            'title'          => '',
            'tagline'        => '',
            'description'    => '',
            'logo'           => '',
            'cover'          => '',
            'gallery'        => '',
            'email'          => '',
            'phone'          => '',
            'website'        => '',
            'location'       => '',
            'location_coord' => '',
            'region'         => '',
            'category'       => '',
            'tags'           => '',
            'price_value'    => '',
            'product'        => '',
            'job'            => '',
            'event'          => '',
        ];
        
        
        $workhours['title'] = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $workhours['id'] = ['m', 't', 'w', 'tt', 'f', 'st', 'sn'];
        $workhours_active = tab_active($workhours, 0);
        
        fusion_load_script(INFUSIONS.'Directory/assets/js/listing-form.js');
        // how to save to which table.. listing table?
        
        
        return [
            1 => [
                'id'    => 'general',
                'title' => 'General',
                'icon'  => 'far fa-edit',
                'field' => [
                    form_text('title', 'Title', $data['title'], ['placeholder' => 'Name this listing', 'required' => TRUE, 'class' => 'm1'])
                    .form_text('tagline', 'Tagline', $data['tagline'], ['placeholder' => 'Something trendy and catchy, a slogan, or motto', 'required' => TRUE, 'class' => 'm1'])
                    .form_editor('description', 'Description', $data['description'], ['type' => 'bbcode', 'required' => TRUE])
                ]
            ],
            2 => [
                'id'    => 'assets',
                'title' => 'Images',
                'icon'  => 'far fa-camera',
                'field' => [
                    // on change must file validate using phpfusion stuff.
                    form_fileinput('logo', 'Logo', $data['logo'], ['required' => TRUE])
                    .form_fileinput('cover', 'Cover Image', $data['cover'])
                    .form_fileinput('gallery', 'Gallery Images', $data['gallery'])
                ]
            ],
            3 => [
                'id'    => 'contact',
                'title' => 'Contact Information',
                'icon'  => 'far fa-mobile',
                'field' => [
                    // on change must file validate using phpfusion stuff.
                    form_text('email', 'Email', $data['email'], ['placeholder' => 'Your email address', 'required' => TRUE])
                    .form_text('phone', 'Phone number', $data['phone'], ['type' => 'number', 'required' => TRUE])
                    .form_text('website', 'Website (optional)', $data['website'], ['placeholder' => 'Your website URL'])
                ]
            ],
            4 => [
                'id'    => 'social',
                'title' => 'Social Networks (optional)',
                'icon'  => 'far fa-mobile',
                'field' => [
                    $this->social_network_field($data)
                ]
            ],
            5 => [
                'id'    => 'hours',
                'title' => 'Work hours',
                'icon'  => 'far fa-mobile',
                'field' => [
                    opentab($workhours, $workhours_active, 'workhours')
                    .$this->workhoursTab($workhours, $workhours_active)
                    .closetab()
                ]
            ],
            6 => [
                'id'    => 'location',
                'title' => 'Location',
                'icon'  => 'far fa-mobile',
                'field' => [
                    form_text('location', 'Location', $data['location'], ['placeholder' => 'e.g. KLCC, Kuala Lumpur', 'append_value' => '<i class="far fa-crosshairs"></i>', 'append' => TRUE]),
                    form_hidden('location_coord', $data['location_coord'], '101.712236,3.151902'),
                    "<div class='p-l-15'><small>Enter coordinates manually</small></div>".
                    display_mapbox('formmap', 5.9788, 116.0753, 13, [
                        'user_location_input' => '#location-field .input-group-addon i',
                        'location_inputname'  => 'location',
                        'coord_inputname'     => 'location_coord',
                    ])
                    //form_location('region', 'Region (optional)', '')
                ]
            ],
            7 => [
                'id'    => 'list',
                'title' => 'Listing details',
                'icon'  => 'far fa-mobile',
                'field' => [
                    form_select('category', 'Category', $data['category'], ['placeholder' => 'Select categories']),
                    form_select('tags', 'Tags (optional)', $data['tags'], ['multiple' => TRUE])
                ]
            ],
            8 => [
                'id'    => 'others',
                'title' => 'Other',
                'icon'  => 'far fa-mobile',
                'field' => [
                    form_select('price_value', 'Price Range (optional)', $data['price_value'], [
                        'options' => [
                            1 => '$ Least expensive',
                            2 => '$$ Normal',
                            3 => '$$$ Premium'
                        ]
                    ]),
                    form_select('product', 'Products (optional)', $data['product'], [
                        'options' => []
                    ]).
                    form_select('job', 'Jobs (optional)', $data['job'], [
                        'options' => []
                    ]).
                    form_select('event', 'Events (optional)', $data['event'], [
                        'options' => []
                    ])
                ]
            ]
        ];
    }
    
    private function social_network_field($data) {
        
        $html = '<div id="social-network-field-wrapper">';
        
        for ($i = 0; $i < count($_SESSION['social_network']); $i++) {
            $value_1 = $_SESSION['social_network'][$i] ?? '';
            $value_2 = $_SESSION['social_url'][$i] ?? '';
            
            $html .= '<div data-row="'.$i.'" class="social-network">'
                .form_select('social_network[]', '', $value_1, [
                    'input_id'    => 'socialy_'.$i,
                    'allowclear'  => TRUE,
                    'placeholder' => 'Select Network', 'width' => '100%', 'inner_width' => '100%',
                    'options'     => social_networks()
                ])
                .form_text('social_url[]', '', $value_2, [
                    'placeholder' => 'Enter URL...',
                    'input_id'    => 'socialx_'.$i
                ])
                ."<a href='#' data-crows='$i' data-action='network_rm' class='trash-social btn btn-default'><i class='far fa-trash'></i></a>"
                .'</div>';
            
        }
        $html .= '</div>';
        
        $html .= '<div class="form-group">'
            .form_button('add_social', 'Add Network', '#', ['class' => 'btn-default btn-md btn-block'])
            .'</div>';
        
        return $html;
    }
    
    private function workhoursTab($workhours, $workhours_active) {
        $range = range(0, 6);
        $html = '';
        foreach ($range as $index) {
            $html .= opentabbody($workhours['title'][$index], $workhours['id'][$index], $workhours_active);
            $html .= $this->hourField($index);
            $html .= closetabbody();
        }
        return $html;
    }
    
    private function hourField($day) {
        
        $html = form_checkbox('workhours_time['.$day.']', '', '', [
            'input_id' => 'workhours_t_'.$day,
            'class'    => 'radio-control',
            'data'     => [
                'day' => $day
            ],
            'options'  => [
                1 => 'Enter hours',
                2 => 'Open all day',
                3 => 'Closed all day',
                4 => 'By appointment only'
            ], 'type'  => 'radio']);
        
        $html .= "<div data-day='$day' class='hourfield' style='display: none;'>";
        // rows
        $last_index = 0;
        $last_end_time = 0;
        
        if (!empty($_SESSION['opening_hours'][$day])) {
            
            $working_time = working_time();
            
            for ($i = 0; $i < count($_SESSION['opening_hours'][$day]); $i++) {
                
                if ($last_end_time == '23:45') {
                    break;
                }
                
                $input_value_1 = $_SESSION['opening_hours'][$day][$i] ?? '';
                $input_value_2 = $_SESSION['closing_hours'][$day][$i] ?? ''; // 00:30
                
                // Sets the hour multiple options
                if ($last_end_time) {
                    $keys = array_keys($working_time);
                    foreach ($keys as $index => $val) {
                        if ($last_end_time === $val) {
                            $last_index = $index;
                            break;
                        }
                    }
                    $starting_hours_opts = array_slice($working_time, $last_index, 99, TRUE);
                    $ending_hour_opts = array_slice($working_time, $last_index + 1, 99, TRUE);
                    
                } else if (!$i) {
                    // this is first
                    $starting_hours_opts = $working_time;
                    $ending_hour_opts = $working_time;
                }
                
                if ($input_value_2) {
                    $last_end_time = $input_value_2;
                }
                
                
                $html .= '<div data-row="'.$i.'" data-day="'.$day.'" class="work-hours">'
                    .form_select('opening_hours['.$day.'][]', '', $input_value_1, [
                        'input_id'    => 'opening_hours_'.$day.'_'.$i,
                        'placeholder' => 'Opens From',
                        'options'     => $starting_hours_opts ?? $working_time
                    ])
                    .form_select('closing_hours['.$day.'][]', '', $input_value_2, [
                        'input_id'    => 'closing_hours_'.$day.'_'.$i,
                        'placeholder' => 'Closes From',
                        'options'     => $ending_hour_opts ?? $working_time
                    ])
                    ."<a href='#' data-id='$i' data-day='$day' data-action='hours_rm' class='trash-hours btn btn-default'><i class='far fa-trash'></i></a>"
                    .'</div>';
                
            }
        }
        
        if ($last_end_time != '23:45') {
            $html .= "<div class='button-wrapper'>".form_button('add_hours', 'Add hours', $day, [
                    'input_id' => 'addhours'.$day,
                    'data'     => [
                        'action' => 'add-hours',
                        'crows'  => $day
                    ],
                    'class'    => 'btn-default btn-md btn-block']).'</div>';
        }
        
        $html .= "</div>";
        
        return $html;
    }
    
    
    private function packageInfo() {
        
        if (get('t') == 'place') {
            return [
                'package' => [
                    [
                        'link'        => BASEDIR.'add-listing.php?t='.get('t').'&v=1',
                        'title'       => 'Free Place Listing',
                        'img'         => '',
                        'price'       => 'FREE ',
                        'description' => [
                            'One listing submission',
                            'Any listing type',
                            'Usable for claiming',
                        ]
                    ],
                    [
                        'link'        => BASEDIR.'add-listing.php?t='.get('t').'&v=2',
                        'title'       => 'Basic Place Plan',
                        'img'         => '',
                        'price'       => '199.90',
                        'description' => [
                            'One listing submission',
                            'Any listing type',
                            '1 year expiration',
                            'Unlocks Events, Jobs and Courses',
                            'Full item management rights',
                            'Featured listing',
                        ]
                    ],
                    [
                        'link'        => BASEDIR.'add-listing.php?t='.get('t').'&v=2',
                        'title'       => 'Commercial Place Plan',
                        'img'         => '',
                        'price'       => '399.90',
                        'description' => [
                            'One listing claim or submission',
                            'Any listing type',
                            '1 year expiration',
                            'Unlocks Events, Jobs and Courses',
                            'Full item management rights',
                            'Featured listing',
                            'Sponsored listing',
                        ]
                    ]
                ]
            ];
            
        } else if (in_array(get('t'), ['event', 'course'])) {
            return [
                'package' => [
                    [
                        'link'        => BASEDIR.'add-listing.php?t='.get('t').'&v=1',
                        'title'       => '1 Listing',
                        'img'         => '',
                        'price'       => '19.90',
                        'description' => [
                            'One listing submission',
                            'Any listing type',
                            '90 days expiration',
                            'Usable for claiming',
                        ]
                    ],
                    [
                        'link'        => BASEDIR.'add-listing.php?t='.get('t').'&v=2',
                        'title'       => '5 Listing',
                        'img'         => '',
                        'price'       => '89.00',
                        'description' => [
                            'Five listing submission',
                            'Any listing type',
                            '180 days expiration',
                            'Featured listing',
                        ]
                    ],
                    [
                        'link'        => BASEDIR.'add-listing.php?t='.get('t').'&v=2',
                        'title'       => '20 Listing Plan',
                        'img'         => '',
                        'price'       => '129.00',
                        'description' => [
                            'Twenty listing submission',
                            'Any listing type',
                            '180 days expiration',
                            'Featured listing',
                        ]
                    ]
                ]
            ];
        }
        
    }
    
    
}
