Notices
========
12/11/15 - Reduced infusions dependencies on core locale files. 
Blog, Downloads, Gallery, Forum, News settings page are now running on their own locale.

Please just update on your end and merge. 

````
git pull origin
git merge
git push
````

or for clones

````
git fetch https://www.github.com/PHP-Fusion/php-fusion.git 9.00
git merge
git push
````

For Ukranian and Russian, everything has been translated and moved accordingly. Lithuanian and Danish are partial translated, but file integrity is checked. If there are any errors, please refer to the English version.


PHP-Fusion Developers Only Version
===================================
PHP-Fusion 9.00 is currently under active development. The first version of Beta release is launched in 3rd Quarter of 2014.
The PHP-Fusion 9.00 adds SEO permalinks, security countermeasures, and form building components. Templates are introduced to increase design capabilities.
Added Features in the Version 9.00 includes blog and e-commerce.

PHP-Fusion is a light-weight open-source content management system (CMS) founded by Nick Jones (also known as Digitanium) in PHP. It uses a MySQL database to store a web site's content and comes with a simple but comprehensive administration system. PHP-Fusion includes features common in many other CMS packages.


Using Github Development Repository
====================================
<strong>Updating your own PHP-Fusion Fork</strong>
<ol>
    <li><strong>Requirement</strong>:
    <ul>
        <li>You have forked the repository into your Github Account</li>
        <li>You have installed Github for [Windows](https://desktop.github.com/)/Mac or installed [GitSCM](https://git-scm.com/downloads) to access latest GitBash version</li>
        <li>Execute your Gitbash command line terminal
        <ol>
        <li><strong>For Mac :</strong> Open Terminal or simply type <strong>terminal</strong> in Spotlight/Finder</li>
        <li><strong>For PC:</strong> Start, and type <strong>cmd</strong> in Searchbox.</li>
        <li>As a result opened a <strong>Terminal or Dos or GitBash</strong> and then use <strong>cd</strong> command to direct yourself into the directory of the working directory of your forked repository (i.e. C:\User\user\Documents\Github\PHP-Fusion\ > - )
    </ul>
	</li>
    <li><strong>Now, Execute and run line by line</strong>
<ol>
<li>git init</li>
<li>git checkout 9.00</li>
<li>git pull upstream</li>
<li>git push</li>
<li>Type <strong>username</strong> and <strong>password</strong> (if available)</li>
</ol>
<strong>Example:</strong>
<ol>
<li>C:\User\user\Documents\Github\PHP-Fusion\ git init</li>
<li>C:\User\user\Documents\Github\PHP-Fusion\ git checkout 9.00</li>
<li>C:\User\user\Documents\Github\PHP-Fusion\ git pull upstream</li>
<li>C:\User\user\Documents\Github\PHP-Fusion\ git push</li>
<li>Type <strong>username</strong> and <strong>password</strong> (if available)</li>
</ol>
</li>
</ul>

Development Changes (Version 9)
================================
<strong>Built to Perform</strong>
<ul>
<li><strong>Faster Core</strong> - Streamlined to perform. First up, new class autoloader for class have been implemented to use namespace instead of server siding files via file paths. Files inclusion for core functions can be now reused without need to call maincore.php</li>
<li><strong>Stronger and Easier Coding</strong> - Automated components without much of html coding. Creating forms is easier than before, with parts and standard form components functions built to encompass almost every single attribute HTML possess, and almost every single jquery implementations in a form field.</li>
<li><strong>360 degree Automatic Sanitization</strong> - The PHP-Fusion Defender outlines everything for you. No more custom sanitization or file upload validation. If we can do it by the core, we do it by the core.</li>
<li><strong>new PDO</strong> - New PDO support for MSQLi server base.</li>
<li><strong>Timezones</strong> - Server Offsets are now redefined by Timezones so DST taken into consideration in our native functions such as showdate(), timer(), countdown(), etc</li>
<li><strong>Consolidated functions</strong> - Custom built native functions supported since version 6,7 are revised, deprecated, merged.</li>
<li>Bootstrapped & Resposive Design</strong> - All core templates improvised to adapt to responsive design. It can be turned off to support older generation theme or other responsive framework such as Foundation, grid360, Semantic UI etc. However, please note that our core systems are built to adapt to Bootstrap only.
</ul>

PHP-Fusion offers users the opportunity to expand the standard packages with so-called "infusions". These infusions can be easily uploaded, installed, and managed. There are a lot of infusions available, a reasonable amount has also been checked to work with PHP-Fusion and may thus be found in the official PHP-Fusion Mods Database. Next to infusions, there are mods, which mostly alter core code, and panels, which appear on either one of the side bars. These are both also widely available, and checked and posted in the PHP-Fusion Mods Database.
PHP-Fusion also offers to create themes and use them on their web site, without much hard work. There are two files, theme.php and styles.css, in which most of the theme can be defined and altered.

Main Features
=========
PHP-Fusion has the following major features:
<ul>
<li>News</li>
<li>Blog</li>
<li>E-commerce</li>
<li>Articles</li>
<li>Forums</li>
<li>Photogallery</li>
<li>Web Links</li>
<li>Downloads</li>
<li>Polls</li>
<li>Shoutbox</li>
<li>PM</li>
<li>Search</li>
<li>Themes</li>
</ul>

New Features 9.00
==================
<strong>Pro-Developer Functions</strong><br/>
<p>We understand that CMS are designed to adapt to developer's use primarily to service custom requirements. As such, at this new version, we worked forward to restructure the CMS to get more and more out of PHP-Fusion, with focus on:</p>
- Maximum Development Productivity
- Maximum Development Speed
- Ease of Use.

<strong>New Core</strong><br/>
PHP-Fusion 9.00 is powered by four subsidiary core extra after maincore.php, introduced in this version.<br/>
- The Dynamic Output handling Class.
- The PHP-Fusion Quantum-Dynamic Field Class.
- The PHP-Fusion Defender Class.
- The PHP-Fusion Atom theme Class.
- The PHP-Fusion Autoloader Class.

<strong>New Installer</strong><br/>
A new installer have been designed and developed to cater for the specific needs:<br/>
- Installation/Uninstallation of Core System Modules.
- Transfer Ownership of Website to any other person without giving out the original password (Rewrite password method).
- Upgrade From previous versions - is decided to be moved here out of 2 primary reason
  -- config.php will be renamed thus shutting down site and not interfering DB overwrite.
  -- fast-in-fast-out. To ensure deletion of the installer tool after core modifications to the website to which at no times, no visitor of the site will be able to intervene with the progress, and that Users do not take lightly of these process out of security reason.

<p>Note that backup of Database will remain in Admin Panel as it does not intervene with the website running. Administrator can always give the site a maintanence shut down if preferred.</p>

<strong>New Themes SDK</strong>
<p>Now more robust and everything can be customized. From previous Version 7.00, extended render_news() and render_articles(), now added in Version 9.00:</p>

<strong>Articles:</strong>: render_article(), render_articles_main(), render_articles_category()<br/>
<strong>Blog:</strong>: render_blog(), render_main_blog(), render_blog_item()<br/>
<strong>Downloads:</strong>: render_downloads(), most_downloadded(), most_recent_downloaded(), filter_item_list()<br/>
<strong>Forum (Part A:Forms)</strong>: post_preview(), postform()<br/>
<strong>Forum (Part B: index)</strong>: render_forum(), searchbar(), render_forum_main(), render_forum_item_type(), forum_viewforum(), render_thread_item()<br/>
<strong>Private Messages</strong>: render_inbox(), render_chatlist()<br/>
<strong>News:</strong> render_news(), render_main_news(), render_news_item()<br/>
<strong>User Profiles: </strong>  render_userprofile(), render_userform()<br/>
<strong>Weblinks:</strong> render_weblinks(), render_weblinks_item()<br/>
<br/>
If you want to customize any part, include your functions into your theme to override the defaults.

<strong>New Admin Panel</strong><br/>
<ul>
<li><strong>Themed</strong> : The default paths to include your admin theme is /themes/admin_templates/</li>
<li><strong>Responsive(ly) Designed</strong> : The new stock standard theme is Venus Admin Panel.</li>
<li><strong>Dashboard</strong> : New Admin Dashboard interface.</li>
<li><strong>Admin Login and Logout</strong>: Extended the security and removed the need to enter any admin password once login.</li>
</ul>

<strong>Stronger User Fields Model</strong><br/>
The User Fields now are dynamic and can be added without modules. Just add them via Admin Panel.

<strong>Theme Engine</strong><br/>
Added the capability to modify default css of themes.

<strong>Multilanguage</strong><br/>
We support multilang in Version 9.00, with core functions built to evolve around it. Due to compatibaility reason, we did not implore any new SDK, or API, but simply
extend links requests to change user's viewing language via "?lang=English" / "?lang=Russian" to switch user preferred language model. Content administration including
User Field creations, panel language switching will adapt to this method and will switch accordingly. However, if a content is made available to English, it will not appear
in any other language, until another content is made available. We treat each language of articles/news/threads - seperately.

<strong>SEO Integration</strong><br/>
Implemented its first step into system-wide modular-base SEO permalinks. keywords and meta are added throughout the system.

<strong>New Submenu System</strong>
During Beta 4, Developer Team pushed a new hierarchy menu navigation system into Version 9.00.

New Core Systems
=================
- E-commerce System - New Version, with Ajax and written in OOP implementations. The E-shop delivers MVC templatable.
 -- Templatable Core
 -- Unlimited Hierarchy
 -- New Ajax Cart Panels
 -- New Administration Interface

- Blog System
- Navigational Sub-Menu System

Highlighted changes New Change Core Systems
============================================
- Forum - added unlimited nesting of forums. unlimited forum hierarchy levels, and added 2 new types of forum - Answer & Support and Links.
- Parse User will now parse User with just using @username in forum, shoutbox, or any applications that uses parseSmileys();

Future
======
PHP-Fusion is a lightweight CMS which have been used because of its lightning-fast performance in server loading times, because we keep our codes light, and compressed.
Even with functions newly developed to do more foundation work, it is very possible that the older ones to be marked for deprecation. We give or take a timeframe for such.
When all foundation work have been set into motion, our future development versions will be moving forward with jquery, ajax and mobile.
