## SiteLinks Documentation

PHP-Fusion 9 encompassed a direct relationship with the older version **showsublinks()** compatibility in backwards themes support. The difference being the overall improvements that has sets apart the version 7 and the version 9 in features.

## showsublinks( $sep , $class, $options, $id)
**File**: incudes/theme_functions_include.php   
**Template Support**: Yes

| Basic Functions 	| 	API 	|	 
|---|---|
| Display Navbar | showsublinks( ````$sep````, ````$class````, ````$options````, ````$id````) | 

#### $options parameter

| 	Parameter API	| Parameter Description 	| Value Type 	| 
|	---		|	---			|	---	|
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

### Usage Example:

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

### Advanced usage of using the showsublinks() function running customized parameters:
The showsublinks can actually be customized to show only a preferred set of array item.

The custom ID position are actually stored as ````link_position```` column in ````DB_SITE_LINKS```` (i.e. tableprefix_site_links) of your SQL table.
Let's say we want to obtain specific conditional records, we can actually obtain hierarchy data through the ````get_SiteLinksData()```` function
of the PHP-Fusion SiteLinks Class.

### **Usage Example:**

Show only items that has the link_position of 6.

````$siteLinks_items_example = (array) \PHPFusion\SiteLinks::get_SiteLinksData(array('link_position'=>array(6)));
echo showsublinks("", "", array('callback_data' => $siteLinks_item_example));````


(Continued')...