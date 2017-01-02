# A guide to Templates in PHP-Fusion 9

Do you know?
<blockquote>
Theme.php is the Master File of PHP-Fusion CMS, to which other files are actually suppporting serving contents to it. 
Maincore.php is a slave to theme.php. So if you define anything or mutate anything in your current active theme file, everything just changes.
</blockquote>
Logic:
<blockquote>
A pure implementation in Version 7 of this is <code>define("THEME_BULLET", "&middot;");</code>
</blockquote>

## The template, and how it runs.
When your site browses to a certain file, theme.php will be first that PHP-Fusion will run. A theme file contains the render_page() function 
that is used for buffering and parsing the output, substituting the contents of the render_page() function accordingly. 

And therefore, by including any custom functions along in theme.php, these functions will in fact register first hand before any other code in the CMS executes.

After the theme functions registered fully, next comes the default template functions we have prepared in default of your theme's functions. Each parts and core components of the PHP-Fusion CMS
comes with its own default template function. So, if your theme do not have these functions yet, it will use these default ones.
However, if your theme have them, the default functions will not load.

A sample custom function:

File: theme.php
````$xslt
// add before or after render_page();
function render_news($info) {
echo 'Custom Functions Is Now Running';
echo 'The things that you can access here is ..'.print_p($info);
}
````
Add them into any of the active theme, and infuse news infusion and run the news. You will see your custom template function takes over the default one. Remove the above code from your theme.php and the stocked default ones will run again.

An example theme that utilizes template overrides extensively is the Atom-X theme. The entire logic has been transported and impelemented as core standards in PHP-Fusion Version 9.


