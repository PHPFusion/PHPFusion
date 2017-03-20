<?php
namespace ThemePack\Nebula\Templates;

use PHPFusion\OutputHandler;
use PHPFusion\Panels;
use ThemeFactory\Core;

class Submissions {

    public static function display_news_submissions_form(array $info = array()) {

        $html = "
        <div class='row'><div id='submit_header' class='panel panel-default elevated' role='navigation'>
        <div class='panel-body'><div class='container'>
            <div class='display-inline-block'><h3 class='text-light m-0'>{%title%}</h3></div>
            <div class='display-inline-block pull-right'>{%news_submit%} {%preview_news%}</div>                   
        </div></div>
        </div></div>
        ";

        $html .= "
        <div class='row'><div id='toolbar' class='panel panel-default elevated' role='navigation'>
        <div class='panel-body'><div class='container text-center'>
            <div class='tool news_news-tool'>
            ".display_html('submit_form', 'news_news', TRUE, TRUE, TRUE, FALSE)."
            </div>
            <div class='tool news_body-tool' style='display:none;'>
            ".display_html('submit_form', 'news_body', TRUE, TRUE, TRUE, FALSE)."
            </div>                                           
        </div></div>
        </div></div>
        ";

        $html .= "
        <div class='row well p-0 m-b-0'>
        <div class='container'>
        <div class='alert alert-warning spacer-xs'>{%guidelines%}</div>
        </div>  
        </div>
        <div class='row'>
        <div class='submit_imageWrapper'>
        <div class='container'>        
        {%news_image_field%}
        </div></div></div>
        
        <div class='row'>
        <div class='submit_body'>
        <div class='container'>
        ".form_text('news_subject', '', '', ['required' => TRUE, 'placeholder' => 'News Headline'])."        
        ".form_textarea('news_news', '', '', ['required' => TRUE, 'placeholder' => 'Write a short and catchy new introduction here.'])."
        ".form_textarea('news_body', '', '', ['required' => TRUE, 'placeholder' => 'Write the Full News Text Here'])."        
        </div></div></div>
                
        <div class='row'>
        <div class='submit_attr'>
        ".opencollapse('submit_prop')."
        ".opencollapsebody('Submissions Properties', 'prop_1', 'submit_prop', 0)."
        <div class='container clearfix'>
            <div class='row spacer-md'>
            <div class='col-xs-12 col-sm-6'>
            {%news_keywords_field%}        
            {%news_language_field%}
            </div><div class='col-xs-12 col-sm-6'>
            {%news_cat_field%}
            {%news_image_align_field%}        
            </div></div>
        </div>        
        ".closecollapsebody()."
        ".closecollapse()."
        </div>
        </div>";

        add_to_jquery("
        $('#toolbar').affix({
            offset: { top: 250, bottom:50 }
        });
        $('#submit_header').affix({
            offset: { top: 250, bottom:50 }
        });
        
        $('textarea').focus(function(e) {            
           var id = $(this).attr('id');
           $('.tool').hide();
           $('.tool.'+id+'-tool').show();           
        });
        ");

        return $html;
    }
}