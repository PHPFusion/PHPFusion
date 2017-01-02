# Template Guides for Search Engine Results
Last Updated: 31.Dec.2016

**Introduction**

The entire search is templateable to customize the whole outlook and assume control of the design output during render time. 
To those who do not understand what are templates, they are like magic methods allows your website to be customized without touching or modification to the core codes.
Themes uses these method to make their themes appear 'different' than others themes.

There are 2 methods that you can use for your customization.

## Available Templates

|  Customizable Sections | Override Function | Protected Override Variables | 
|---|---|---|
| The entire body layout | render_search() | Search::$render_search | 
| No results text | render_search_no_result() | Search::$search_no_result |
| Search count bar | render_search_count() | Search::$search_count |
| Search Item Layout Type 1 | render_search_item() | Search::$search_item |
| Search Item Layout Type 2 | render_search_item_list() | Search::$search_item_list |
| Search Item Layout Type 3 | render_search_item_image() | Search::$search_item_image |
| Search Item Container | render_search_item_wrapper() | Search::$search_item_wrapper |

## How it works?
There are 2 types of override method you can use. 
###Method 1:  Variable Mutations via Override Variables.
Also refers as the "private scope" override method.

The first type of mod can be achieved by simply mutating the designed 'override template variables'.
 
PHP-Fusion allows you to extend the Search Engine and modify its output in a **seperate instance.** (i.e your custom work only based on the search engine).
One of the most major changes in PHP-Fusion 9 is that we have began to reconstruct the entire CMS into object so that you can clone these core features in your own custom scope without having to reproduce the whole original algorithm. This is call "extending" (or. in layman terms, borrowing the code) so your code do not have to be lengthy and still output the original but in which the author want it to serve a different purpose.

<blockquote>
Case References:
The new PHP-Fusion Main site QuickFinder Toolbar is an example of this. The entire Infusion has been made around 200-300 lines of codes, whereas Search Engine itself is heavy and contains some gruelsome nightmarish algorithm. By extending to this class, we were able to skip and implement our codes straightforward manner. 
</blockquote>

For those who are 'extending' the Search Engine class, you can simply use the this method to quickly customize the output of the search engine on your custom project.
However, you must know that this is only binding on your own Class scope. In simpler terms, imagine you have a "book". By extending, you're putting that book through a photocopier machine, and prints out each page in the same, and you start cutting out chapters and reshuffle them and then make that Book different. 

**Please note that search.php is running its own instance
and therefore will not be affected by this scope of change.** In other words, whatever happens in your book, is in your book only. It does not affect the original book which the search.php is using.

Sample code:
````$xslt
class MyCustom_SearchInfusion extends \PHPFusion\Search\Search_Engine {
    public function display_output() {
        // This will modify the output of your $search_item_wrapper
        parent::$search_item_wrapper = "<div class='list-group-hover'>{%search_content%}</div>";
        // now your search item wrapper is different.                
    }
}
````

###Method 2: Make new override function

PHP-Fusion Templates function exists since 15 years ago. These template functions are referred as "render_news(), render_comments(), etc." and is of nothing new. Please refer to the relevant documentation
for more information regarding the methods.

For theme developers who wishes to customize your theme output for search **globally** throughout your theme functionality, you should use the template override functions.

**Sample code: Theme.php**
````$xslt
// add before render_page

function render_search() {
return open_table('{%title%}')."
        <div class='my_custom_template'>        
        <div class='clearfix m-b-15'>{%search_text%}</div>        
        {%search_method%}
        {%search_button%}
        </div>
        <div class='row'>
        <div class='col-xs-12 col-sm-6'>
            <div class='well'>
            {%search_sources%}
            </div>
        </div><div class='col-xs-12 col-sm-6'>
            <div class='well'>
            {%search_areas%}
            </div>
            <div class='well'>
            {%sort_areas%}
            </div>
            <div class='well'>
            {%char_areas%}
            </div>
        </div></div>
        ".close_table();
";
}
````
This method is a global method. It affects the whole of
PHP-Fusion automatically register the function into the server and will automatically generate the new search output as to your new design as above.

We have removed the <code>$info['whatever-key']</code> method in the search.php function as it was not necessary to speed buffering since parsing all search modules during search is very SQL intensive.

## Available Macro Keys
(Further documentation in the future...) 

Please refer to includes/classes/PHPFusion/Search.inc to find the macro keys available for now.