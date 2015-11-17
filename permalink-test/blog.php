<?php
// Blog page
// code whatever links to be translated here
require_once "../maincore.php";
require_once THEMES."templates/header.php";
require_once THEMES."templates/global/home.php";
require_once INCLUDES."infusions_include.php";
include LOCALE.LOCALESET."homepage.php";
add_to_title($locale['home']);
require_once INCLUDES."infusions_include.php";

opentable("All Possible Blog Links");

echo "<h4>Declared under Pattern Rules</h4>\n";
echo "<a href='".INFUSIONS."blog/blog.php'>Blog Link</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?readmore=1'>Readmore Blog Link</a><br/>";
echo "<a class='btn btn-default btn-sm' href='print.php?type=B&amp;item_id=1'>Print Blog Link</a><br/>";

echo "<h4>Pattern Rules Not Yet Updated - 17/11/15</h4>\n<br/>";
echo "<a href='".INFUSIONS."blog/blog.php?type=recent'>Most Recent Blogs Link</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?type=comment'>Most Commented Blogs Link</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?type=rating'>Most Rated Blogs Link</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?archive=2015&month=11'>November 2015</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?author=1'>Author - Super Admin</a><br/>";
closetable();

opentable("All Possible Blog Category Links");

echo "<a href='".INFUSIONS."blog/blog.php?cat_id=0'>Uncategorized</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?cat_id=1'>Some category</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?cat_id=2'>Some category 2</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?cat_id=3'>Some category 3</a><br/>";
echo "<a href='".INFUSIONS."blog/blog.php?cat_id=4'>Some category 4</a><br/>";

closetable();

require_once THEMES."templates/footer.php";