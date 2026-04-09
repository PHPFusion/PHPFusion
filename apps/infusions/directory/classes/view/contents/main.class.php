<?php
namespace PHPFusion\Infusions\Directory\Classes\View\Contents;
use PHPFusion\Infusions\Directory\Classes\View\Home;

/**
 * Class home contents
 */
class Main extends Home {
    /**
     * @return string
     */
    public function display(): string {
        return fusion_render(THEME_TEMPLATES, 'home.twig', $this->getInfo(), TRUE);
    }
    
    /**
     * @return array
     */
    public function getInfo(): array {
        // the infusions for schools directory
        // we need a category
        $info['event'][0] = [
            'image'     => IMAGES.'events/photo-1.jpg',
            'link'      => BASEDIR.'home.php?type=event&r=1',
            'title'     => "London Grammer live",
            'location'  => "103 Gaunt Street",
            'datetime'  => "March 7, 2021 12:00AM - March 9,2012 12:00 AM",
            'type'      => 'Upcoming',
            'host_name' => 'Ministry of Sound',
            'host_logo' => IMAGES.'events-logo/uk-ministry.jpg'
        ];
        
        $info['school'][0] = [
            'image'       => IMAGES.'places/srjkstjames.jpg',
            'link'        => BASEDIR.'home.php?type=school&r=1',
            'title'       => "SRJK St. James",
            'description' => lorem_ipsum(50),
            'location'    => "Likas, Kota Kinabalu",
            'status'      => 'Open',
            'type'        => 'Primary School',
            'avatar'      => IMAGES.'events-logo/msclogo.png',
            'host_logo'   => IMAGES.'events-logo/uk-ministry.jpg'
        ];
        
        $info['job'][0] = [
            'job_name'    => "BM Tutor",
            'link'        => BASEDIR.'home.php?type=jobs&r=1',
            'job_logo'    => IMAGES.'events-logo/uk-ministry.jpg',
            'description' => lorem_ipsum(50),
            'job_nature'  => "Everyday",
            'location'    => "Likas, Kota Kinabalu",
            'status'      => 'Open',
            'type'        => 'Primary School',
            'avatar'      => IMAGES.'events-logo/msclogo.png',
        ];
        $info['institution_search_form'] = openform('instSearchFrm', 'GET').
            form_text('search_loc', 'Where to look', '', [
                'autocomplete_off' => TRUE,
            ]).
            form_select('search_type', 'Category', '', [
                'options'  => [
                    'staff'   => 'Staff/Administration',
                    'teach'   => 'Teachers',
                    'student' => 'Students'
                ],
                'input_id' => 's01',
            ]).
            form_text('search_name', 'What are you looking for', '', [
                'input_id' => 'y01'
            ]).
            form_button('post_search', 'Search', 'event', ['class' => 'btn-primary', 'icon' => 'far fa-search', 'input_id' => 's1-4']).
            closeform();
        
        $info['event_search_form'] = openform('eventSearchFrm', 'GET').
            form_text('search_loc', 'What to look', '').
            form_select('search_type', 'Category', '', [
                'options'  => [
                    'expo'       => 'Expo',
                    'outdoor'    => 'Outdoor Activities',
                    'vacation'   => 'Vacation',
                    'meetings'   => 'Meetings',
                    'conference' => 'Conferences',
                ],
                'input_id' => 's02',
            ]).
            form_select('search_duration', 'Show Events from', '', [
                'options' => [
                    'any'      => 'Any Day',
                    'today'    => 'Today',
                    'week'     => 'This week',
                    'wekend'   => 'This weekend',
                    'nextweek' => 'Next week',
                    'custom'   => 'Pick a date',
                ]
            ]).
            "<div id='custom_date_param'>".
            form_datepicker('start_dur', 'From...', '').
            form_datepicker('end_dur', 'To...', '').
            "</div>".
            form_button('post_search', 'Search', 'event', ['class' => 'btn-primary', 'icon' => 'far fa-search']).
            closeform();
        
        add_to_jquery("
        $('#search_duration').on('change', function(ev) {
            if ($(this).val() === 'custom') {
                $('#custom_date_param').addClass('show');
                $('#search_duration-field').hide();
            }
        });
        ");
        
        $info['people_search_form'] = openform('staffSearchFrm', 'GET').
            form_text('search_loc', 'What to look', '').
            form_select('search_type', 'Category', '', [
                'options'  => [
                    'staff'   => 'Staff/Administration',
                    'teach'   => 'Teachers',
                    'student' => 'Students'
                ],
                'input_id' => 's03',
            ]).
            form_text('search_name', 'Name', '', ['input_id' => 'y02']).
            form_button('post_search', 'Search', 'event', ['class' => 'btn-primary', 'icon' => 'far fa-search']).
            closeform();
        
        return $info;
    }
}
