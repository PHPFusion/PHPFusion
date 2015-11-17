<?php
// Blog page
// code whatever links to be translated here
require_once "../maincore.php";
require_once THEMES."templates/header.php";
require_once THEMES."templates/global/home.php";
require_once INCLUDES."infusions_include.php";
include LOCALE.LOCALESET."homepage.php";
require_once INCLUDES."infusions_include.php";

$permalink->debug_regex = true;

add_to_title("Test Page for Blog Permalink");
opentable("All Possible Blog Links");

echo "<div class='well'>\n";
openside("Declared under Pattern Rules");
echo "<ol style='list-style:decimal !important;'>\n";
echo "<li><a href='".INFUSIONS."blog/blog.php'>Blog Link</a></li>";
echo "<li><a href='".INFUSIONS."blog/blog.php?readmore=1'>Readmore Blog Link</a></li>";
echo "<li><a href='print.php?type=B&amp;item_id=1'>Print Blog Link</a></li>";
echo "</ol>\n";
closeside();
echo "</div>\n";


echo "<div class='well'>\n";
openside("Pattern Rules Not Yet Updated - 17/11/15");
echo "<ol style='list-style:decimal !important;'>\n";
echo "<li><a href='".INFUSIONS."blog/blog.php?type=recent'>Most Recent Blogs Link</a></li>";
echo "<li><a href='".INFUSIONS."blog/blog.php?type=comment'>Most Commented Blogs Link</a></li>";
echo "<li><a href='".INFUSIONS."blog/blog.php?type=rating'>Most Rated Blogs Link</a></li>";
echo "<li><a href='".INFUSIONS."blog/blog.php?archive=2015&amp;month=11'>November 2015</a></li>";
echo "<li><a href='".INFUSIONS."blog/blog.php?author=1'>Author - Super Admin</a></li>";
closeside();
echo "</div>\n";
closetable();

opentable("All Possible Blog Category Links");
echo "<div class='well'>\n";
echo "<a href='".INFUSIONS."blog/blog.php?cat_id=0'>Uncategorized</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?cat_id=1'>Some category</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?cat_id=2'>Some category 2</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?cat_id=3'>Some category 3</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?cat_id=4'>Some category 4</a><br/>";
echo "</div>\n";
closetable();

require_once THEMES."templates/footer.php";