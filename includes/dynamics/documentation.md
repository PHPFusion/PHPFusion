Dropdown Select
===
Function API: 
````
form_select($input_name, $input_label, $input_value, array $options)
````

**Change Logs in 9.03:**

**New Auto Options Building**

a. Added support for optgroup (hierarchy) 

b. Added support for hierarchy select. (Our intent is to to deprecate form_select_hierarchy in favor of form_select)

c. The internal option building is directly based on queries, and is instanced to prevent exhaustion on server memory and lightning fast performance.

d. Added back suport for chain queries.

**New $option Parameters for the form_select API**
 
 ````$xslt
        'chainable'   => FALSE,      // Set to True to Enable Chaining
        'chain_to_id' => '',         // Set which select it has to be chained to.
        'db'           => '',         // Specify which DB to map
        'id_col'       => '',         // Chain Primary Key Column Name
        'cat_col'      => '',         // Chain Category Column Name
        'title_col'    => '',        // Chain Title Column Name
        'custom_query' => '',     // If any replacement statement for the query
        'value_filter' => array('col' => '', 'value' => NULL), // Specify if building opts has to specifically match these conditions
        'optgroup'       => FALSE,      // Enable the option group output - if db, id_col, cat_col, and title_col is specified.
        'option_pattern' => "&#8212;",
````

Generate Dropdown Options 
---
These example values are just for basic understanding of the nature of the values only and is not to be accurate implementations:

| Parameters  |   Default Values  |   Description | Example Values |
|---|---|---|---|
| db  |  blank  | sets the database table name  |    DB_NEWS_CATS |
| id_col | blank | define the table primary key | news_cat_id |
| cat_col | blank | define the table parent key | news_cat_parent |
| title_col | blank | define the table title key | news_cat_title |
| custom_query (optional) | blank | replace the whole query with your own | ````SELECT nc.*, n.*, FROM ".DB_NEWS_CATS." nc INNER JOIN ".DB_NEWS." n  ON nc.news_cat_id=n.news_id WHERE news_cat_status=1```` |
| value_filter (optional) | array | if set, will only filter and build only the matched conditions | ````array('col'=>'news_cat_status', 'value' => 1)````|
| optgroup (optional) | false | if set TRUE, will show optgroup format | TRUE | 
| option_pattern (optional) | &#8212 | a dash as default but can be configured to your liking, or reset with a NULL to clear the prefix dash | NULL | 

This will automatically rebuild your dropdown options internally, without the need for extra query to build options. 
The second dropdown select that uses the same DB will not create any additional queries in the server. It will use the cached results earlier.

Chaining Selects
---
The select can be chained to another select only if $options['db'], $options['id_col'], $options['title_col'] is used. This is due to internal formatting required to identify the parents of each option.
Since we have a cached solution, having duplicated form_select with different filtering values is fastest approach without any impact on the performance.

Example :
````
form_select('shipping_country', '', '', [
    'db' => DB_CLASS_LOCATION,
    'id_col' => 'loc_id',
    'cat_col' => 'loc_parent',
    'title_col' => 'loc_title',
    'value_filter' => array(
        'col' => 'loc_type',
        'value' => 2,
    ),
    'placeholder'=>'Country',
    'class'=>'display-inline-block',
    'optgroup' => TRUE,
]).
form_select('shipping_state', '', '', [
    'db' => DB_CLASS_LOCATION,
    'id_col' => 'loc_id',
    'cat_col' => 'loc_parent',
    'title_col' => 'loc_title',
    'chainable' => TRUE,
    'chain_to_id' => 'shipping_country',
    'value_filter' => array(
        'col' => 'loc_type',
        'value' => 1,
    ),
    'option_pattern' => '',
    'placeholder'=>'State',
    'class'=>'display-inline-block'
]).
form_select('shipping_city', '', '', [
        'db' => DB_CLASS_LOCATION,
        'id_col' => 'loc_id',
        'cat_col' => 'loc_parent',
        'title_col' => 'loc_title',
        'chainable' => TRUE,
        'chain_to_id' => 'shipping_state',
        'value_filter' => array(
            'col' => 'loc_type',
            'value' => 0,
        ),
        'option_pattern' => '',
    'placeholder'=>'City', 'class'=>'display-inline-block']
);
`````
The above example uses a hierarchy table query to build a chained select dropdown list of country, state, and city from a table. A filter value restrictions is placed on ```loc_type``` column to differentiate and show only specific results.
In this case, ```loc_type``` with value 2 is a country, 1 is a state and 0 is a city entry. If the filter was not set, it will display dropdown options of all countries, state, city in a single dropdown list as intended through a full hierarchy query output.

                
                
                