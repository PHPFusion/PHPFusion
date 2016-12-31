## SiteLinks Documentation
Last Updated: 27/7/2016

## The PHP-Fusion SiteLinks Class API
Accessible through \PHPFusion\SiteLinks is the libraries of functions for SiteLinks in Version 9 consisting of static functions that developer may be able to use for the development of PHP-Fusion based project. 

### Get current site links sql record from a given URL
````get_current_SiteLinks($url = '', $key = NULL)````

### Get site links sql record from a given link Id
```` load_sitelinks( $link_id ) ````


### Check whether a site link record exist given a site link id
```` verify_sitelinks($link_id) ````

### Delete a site link given a site link id
```` delete_sitelinks($link_id) ````

### Get Link visibility options.
```` get_LinkVisibility() ````

### Get Site Links hierarchy data for showsublinks() function
```` get_SiteLinksData( array $options = array() ) ````

This function allows you to customize your query parameters for the sublinks and outputs a hierarchy format that the showsublinks() function will be able to use to traverse and generate the navigational bar html output.

| Options Parameters |  Default values | Description |
|:---|:---|:---|
|   join             |  empty          | SQL join statement |
|   position_condition | link_position is 2 and 3 | SQL position condition |
|   condition       |  link_language is current site language, link_visibility is as per current member level or less | link_language and link_access check |
|   group           | empty     | group by table column |
|   order           | link_cat ASC, link_order ASC |    order by table column |

#### Example Usage:
To fetch all menu link items that is conditional on existing record on a custom table data that has a position id of 6,9,12
````
$menu_items = \PHPFusion\SiteLinks::get_SiteLinksData(
array(
    'join'  =>  'INNER JOIN ".DB_SITE_LINKS_GALLERY." sm ON sm.gallery_link_id=sl.link_id',
    'position' =>   array(6,9,12),
    'group' => 'sl.link_id',
));

// Show navigational bar output
echo showsublinks('', '', array('callback_data' => $menu_items));
````

# Displaying your site navigational bar


**Introduction:**

PHP-Fusion 9 encompassed a direct relationship with the older version **showsublinks()** compatibility in backwards themes support. The difference being the overall improvements that has sets apart the version 7 and the version 9 in features.

**File**: incudes/theme_functions_include.php
**Template Support**: Yes

### showsublinks( $sep , $class, $options, $id)


| Basic Functions 	| 	API Parameters |
|:---:|:---|
| Display Navbar | showsublinks( ````$sep````, ````$class````, ````$options````, ````$id````) | 

#### $options parameter

| 	Parameter API	| Parameter Description 	| Value Type 	| 
|	:---:		|	:---:			|	:---:   |
| 	id 		| 	the id of the navigational bar		| string |
|	container	| 	fixed container width 				| boolean|
|	navbar_class 	| 	css class of navigational bar 		| string|
| 	item_class	|	css class of navigational list item | string|
| 	seperator	|	seperator type 						| string|
| 	links_per_page	| 	number of  primary links to show 	| integer|
| 	grouping	| 	whether to collate links as "show more"		| boolean|
|	show_banner	| 	whether to show site banner  	| boolean|
|	show_header	|	whether to show header		| boolean|
|	callback_data	|	optional data population	| array|

##### Usage Example:

Default navigational bar by default that contains only '**subheader**' items.

````
echo showsublinks("", "", array(
"navbar_class"=>"navbar-inverse", 
									"container"=>true, 
									"show_banner"=>true, 
									"show_header" => true
									)
					);
````

###### Advanced usage of using the showsublinks() function running customized parameters. 
The showsublinks can actually be customized to show only a preferred set of array item.

The custom ID position are actually stored as ````link_position```` column in ````DB_SITE_LINKS```` (i.e. tableprefix_site_links) of your SQL table.
Let's say we want to obtain specific conditional records, we can actually obtain hierarchy data through the ````get_SiteLinksData()```` function
of the PHP-Fusion SiteLinks Class.

##### **Usage Example:**

Show only items that has the link_position of 6 through the ````get_SiteLinksData())```` formatter function.
````
$siteLinks_items_example = (array) \PHPFusion\SiteLinks::get_SiteLinksData(array('link_position'=>array(6)));

echo showsublinks("", "", array('callback_data' => $siteLinks_item_example));
````

## Templating a custom version of the navigational bar.

If you declare a custom version of the function showsublinks() in your theme, you can actually override the behavior of the default showsublinks() function.
Modifications can be easily done by copying the default function into your own theme. After which, PHP-Fusion 9 will use your version and omit the default
version. This is particularly interesting for the development of a hierarchy based 'Mega Menu' especially in themes and infusion.

## Usage of Token API for Cross Page Requests
For security good concerns, we have implemented Token validation for Page to Page validation of form submissions.
As a **thumb rule**, we only allow any form to submit to itself by default.

If your form is targetting to execute on any remote file other than itself, in openform(), you need to add 
````$options['remote_url]```` as ````fusion_get_settings('site_path').'your-remote-file.php'````
in order for token to be successfully validated. 

Cross Page refers to generally page not itself, or is a remote one, even if it is hosted in the same server and same PHP-Fusion Installation.
This applies to AJAX requests as well.

An example of ajax requests **URL** param for a Ajax Request should be:
````
add_to_jquery("
    function load_results(input) {
        var data = { 'q' : input }
        $.ajax({
            url: '".FUSION_ROOT."your-remote-file.php',
            type: 'GET',
            dataType: 'html',
            data : $.param(data),
            success: function(result) {
                $('#typeahead_result').html(result);
            },
            complete: function(result){
                // do a recent search table
                $('#typeahead').addClass('open');
            },
            error: function() {
                console.log('Typeahead Error');
            }
        });
    }
");
````
**Note:** Const **FUSION_ROOT** which refers to your 'base directory' or 'root folder' is used, due to .htaccess file is always present, and XHR http requests friendly.
