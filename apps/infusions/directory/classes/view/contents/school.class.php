<?php
namespace PHPFusion\Infusions\Directory\Classes\View\Contents;
/**
 * Class HomeSchool
 * Item view for school details
 */
class School {
    /**
     * @return string
     */
    public function display(): string {
        return fusion_render(THEME_TEMPLATES, 'home-school.twig', $this->getInfo(), TRUE);
    }
    
    /**
     * @return array
     */
    public function getInfo(): array {
        
        fusion_load_script(INCLUDES.'jscripts/js.cookie.js');
        
        $r = get('r', FILTER_VALIDATE_INT);
        
        add_to_jquery("
        $('.profile-menu .menu li').on('click', function() {
            var cookieName = 'institution_$r';
            var cookieValue = $(this).find(\"a[role='tab']\").attr('href');
            console.log(cookieValue);
            Cookies.set(cookieName, cookieValue);
        });
        ");
        
        $active_cookie = cookie('institution_'.$r) ?: '#profile_listing';
        
        return [
            'cover'        => IMAGES.'school/campus.jpg',
            'logo'         => IMAGES.'school-logo/ecinema.jpg',
            'contact_form' => $this->contactForm(),
            'gallery'      => $this->gallery(),
            'review_form'  => $this->reviewForm(),
            'reviews'      => '',
            'active_page'  => $active_cookie,
            'event'        => $this->events(),
            'job'          => $this->jobs(),
            'course'       => $this->courses(),
            'actions'      => [
                'directions' => urlencode('https://maps.google.com/maps?daddr=Lot 87 Taman Khidmat, Lorong Pokok Seraya 3A, Kota Kinabalu, Sabah, Malaysia'),
                'tel'        => 'tel:+088385200',
                'web'        => 'https://www.php-fusion.co.uk',
                'shares'     => $this->shareModal(),
                'report'     => $this->reportModal(),
            ],
            'map'          => display_mapbox('map', 116.0763648, 5.9637485, 15, [
                'items' => [
                    [
                        'coordinates' => [116.0763648, 5.9637458],
                        'icon'        => 'theatre',
                        'description' => 'Description of something'
                    ]
                ]
            ])
        ];
        
    }
    
    private function contactForm() {
        return openform('contactFrm', 'POST')
            .form_text('name', '', '', ['placeholder' => 'Your name', 'required' => TRUE])
            .form_text('email', '', '', ['placeholder' => 'Your Email address', 'required' => TRUE, 'type' => 'email'])
            .form_textarea('description', '', '', ['placeholder' => 'Your message', 'required' => TRUE])
            .form_button('send_email', 'Send Message', 'send', ['class' => 'btn-primary btn-md']);
    }
    
    private function gallery() {
        require_once INCLUDES.'gallery/gallery_include.php';
        return display_gallery();
    }
    
    private function reviewForm() {
        
        function rating_select($field_name, $rating_id) {
            
            return "<fieldset class='rate'>
            <input type='radio' id='".$rating_id."_10' name='$field_name' value='10' /><label for='".$rating_id."_10' title='5 stars'></label>
            <input type='radio' id='".$rating_id."_9' name='$field_name' value='9' /><label class='half' for='".$rating_id."_9' title='4 1/2 stars'></label>
            <input type='radio' id='".$rating_id."_8' name='$field_name' value='8' /><label for='".$rating_id."_8' title='4 stars'></label>
            <input type='radio' id='".$rating_id."_7' name='$field_name' value='7' /><label class='half' for='".$rating_id."_7' title='3 1/2 stars'></label>
            <input type='radio' id='".$rating_id."_6' name='$field_name' value='6' /><label for='".$rating_id."_6' title='3 stars'></label>
            <input type='radio' id='".$rating_id."_5' name='$field_name' value='5' /><label class='half' for='".$rating_id."_5' title='2 1/2 stars'></label>
            <input type='radio' id='".$rating_id."_4' name='$field_name' value='4' /><label for='".$rating_id."_4' title='2 stars'></label>
            <input type='radio' id='".$rating_id."_3' name='$field_name' value='3' /><label class='half' for='".$rating_id."_3' title='1 1/2 stars'></label>
            <input type='radio' id='".$rating_id."_2' name='$field_name' value='2' /><label for='".$rating_id."_2' title='1 star'></label>
            <input type='radio' id='".$rating_id."_1' name='$field_name' value='1' /><label class='half' for='".$rating_id."_1' title='1/2 star'></label>
            <input type='radio' id='".$rating_id."_0' name='$field_name' value='0' /><label for='".$rating_id."_0' title='No star'></label>
            </fieldset>";
            
        }
        
        $html = openform('reviewFrm', 'POST')
            ."<div class='review-form-grid'>"
            ."<div class='review-category'><h5>Overall Rating</h5>".rating_select('ratings[1]', 'overall-ratings')."</div>"
            ."<div class='review-category'><h5>Hospitality</h5>".rating_select('ratings[2]', 'hospitality-ratings')."</div>"
            ."<div class='review-category'><h5>Staff Friendliness</h5>".rating_select('ratings[3]', 'friendliness-ratings')."</div>"
            ."<div class='review-category'><h5>Pricing</h5>".rating_select('ratings[4]', 'pricing-ratings')."</div>"
            ."</div>"
            .form_hidden('ratings[1]')
            .form_hidden('ratings[2]')
            .form_hidden('ratings[3]')
            .form_hidden('ratings[4]');
        if (iGUEST) {
            $html .= form_text('name', 'Name', '', ['placeholder' => 'Your Name'])
                .form_text('email', 'Email', '', ['placeholder' => 'Your Email']);
        }
        $html .= form_textarea('message', '', '', ['placeholder' => 'Enter message...']);
        $html .= form_button('submit_review', 'Submit a Review', '', ['class' => 'btn-primary']);
        
        return $html;
    }
    
    /**
     * @return array
     */
    private function events() {
        
        return [
            [
                'image'     => IMAGES.'events/photo-1.jpg',
                'link'      => BASEDIR.'home.php?type=event&r=1',
                'title'     => "London Grammer live",
                'location'  => "103 Gaunt Street",
                'datetime'  => "March 7, 2021 12:00AM - March 9,2012 12:00 AM",
                'type'      => 'Upcoming',
                'host_name' => 'Ministry of Sound',
                'host_logo' => IMAGES.'events-logo/uk-ministry.jpg'
            ],
            [
                'image'     => IMAGES.'events/photo-1.jpg',
                'link'      => BASEDIR.'home.php?type=event&r=1',
                'title'     => "London Grammer live",
                'location'  => "103 Gaunt Street",
                'datetime'  => "March 7, 2021 12:00AM - March 9,2012 12:00 AM",
                'type'      => 'Upcoming',
                'host_name' => 'Ministry of Sound',
                'host_logo' => IMAGES.'events-logo/uk-ministry.jpg'
            ]
        ];
        
    }
    
    /**
     * @return array
     */
    private function jobs() {
        return [
            [
                'job_name'    => "BM Tutor",
                'link'        => BASEDIR.'home.php?type=jobs&r=1',
                'job_logo'    => IMAGES.'events-logo/uk-ministry.jpg',
                'description' => lorem_ipsum(50),
                'job_nature'  => "Everyday",
                'location'    => "Likas, Kota Kinabalu",
                'status'      => 'Open',
                'type'        => 'Primary School',
                'avatar'      => IMAGES.'events-logo/msclogo.png',
            ],
            [
                'job_name'    => "BM Tutor",
                'link'        => BASEDIR.'home.php?type=jobs&r=1',
                'job_logo'    => IMAGES.'events-logo/uk-ministry.jpg',
                'description' => lorem_ipsum(50),
                'job_nature'  => "Everyday",
                'location'    => "Likas, Kota Kinabalu",
                'status'      => 'Open',
                'type'        => 'Primary School',
                'avatar'      => IMAGES.'events-logo/msclogo.png',
            ]
        ];
        
    }
    
    private function courses() {
        return [
            [
                'image'     => IMAGES.'courses/photo-3.jpg',
                'link'      => BASEDIR.'home.php?type=event&r=1',
                'title'     => "London Grammer live",
                'location'  => "103 Gaunt Street",
                'datetime'  => "March 7, 2021 12:00AM - March 9,2012 12:00 AM",
                'type'      => 'Upcoming',
                'host_name' => 'Ministry of Sound',
                'host_logo' => IMAGES.'events-logo/uk-ministry.jpg',
            
            ]
        ];
    }
    
    private function shareModal() {
        return openmodal('share', '', [
                'hidden'       => TRUE,
                'button_class' => 'share-popup',
                'class'        => 'modal-md'
            ])
            ."<ul class='share-options'>"
            .social_media_links(fusion_get_settings('siteurl').'home.php?type='.get('type').'&r='.get('r', FILTER_VALIDATE_INT), [
                'template' => '<li><a class="m-5 {%class%}" href="{%url%}" title="{%name%}" target="_blank" rel="nofollow noopener"><i class="{%icon%}"></i>{%name%}</a></li>'
            ])
            ."</ul>"
            .closemodal();
    }
    
    private function reportModal() {
        return openmodal('report', '', [
                'hidden'       => TRUE,
                'button_class' => 'report-popup',
                'class'        => 'modal-md',
                'dismiss'=>FALSE,
            ])
            ."<h5><strong>Report this Listing</strong></h5>"
            .openform('reportFrm', 'POST')
            .form_textarea('report_description', '', '', ['placeholder' => 'What\'s wrong with this listing?'])
            .form_button('report_btn', 'Submit Report', 'submit_report', ['class'=>'btn-block btn-md btn-primary'])
            .closeform()
            .closemodal();
    }
    
    
}

require_once CLASSES.'Mapbox/mapbox_include.php';
