## OpenGraph Documentation
Last Updated: 27/12/2016
http://ogp.me/ - description

## The OpenGraph class
In first you have create your class in infusion folder which extends OpenGraph:
````class OpenGraphVideos extends OpenGraph````

### Adding a functions
Add static function(s) for you content and for categories if it is needed.
````public static function ogVideo($id = 0)````
````public static function ogVideoCat($id = 0)````

### Creating data array
In your function(s) select from DB required information:
````SELECT `title`, `description`, `keywords`, `url` FROM `VIDEOS` WHERE `id` = '$id'
After that create array named $info with next items:
* title;
* descrioption;
* keywords;
* url;
* image (optionally).
 ATTENTION! Don't place empty values into array.
And call setValues() function:
````OpenGraphVideo::setValues($info)````

### Addind OpenGraph to content item page
Place call of function at content item page after render function:
OpenGraphVideos::ogVideo($_GET['video_id'])

### Default values
Default values with site title, description, keywords etc. are used if you don't use own functions.
