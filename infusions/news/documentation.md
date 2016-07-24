News Infusion
=============
Last Updated : 24/7/2016

This file serves as documenting functions and detailing requirement of features and shortcuts API for specific 
infusion related requirements moving forward. This allows developers in many ways extending the news infusion to
fit custom requirements.

Templates SDK:
The templates SDK are provided in the /templates folder and the Septenary /templates custom_news.php file.

Moving forward template requirements and extending the output, you can explore News Infusion class libraries as for
your common option. 

##### Accessing the News Server Functions
The core functions provided by News driver functions API are served through ```` \PHPFusion\News\NewsServer::News() ```` residing factory. For the purpose of orientation, we shall invoke as 
```` $news = \PHPFusion\News\NewsServer::News() ````  

| Basic Functions   |    API    |   Access  | Options Parameters    |
|   ---             |   ---     |   ---     |       ---             |
|  Fetching a Query | $news->get_NewsQuery($options)  | public | condition, order, limit | 
|  Parsing a Data | $news->get_NewsData( $data )   | public | $data = dbarray($query); |

##### Example code: To fetch a standard news query.
This enables us to pre-select all tables that is required with the current joins and altered with condition, order, and limit parameter
in convenience:
````
$news =  \PHPFusion\News\NewsServer::News();
$result = dbquery( $news->get_NewsQuery(
                        array(
                            'condition'=> 'news_cat=3',
                            'order' => 'news_id',
                            'limit' => 8
                        )
                    );
````

##### Example code: To format a data as per default output 
This enables us to format and output a standard variations of all array/values to be used in your templates.

````
$news =  \PHPFusion\News\NewsServer::News();
$result = dbquery( $news->get_NewsQuery(
                        array(
                            'condition'=> 'news_cat=3',
                            'order' => 'news_id',
                            'limit' => 8
                        )
                    );
$rows = dbrows($result);
if ($rows > 0) {
    while ($data = dbarray($result) {
        $data = $news->get_NewsData($data);
        print_p($data);
    }
}
````