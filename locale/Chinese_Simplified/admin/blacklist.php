<?php
// Delete blacklisted user
$locale['400'] = "Delete user from Blacklist";
$locale['401'] = "User deleted from Blacklist";
$locale['402'] = "Return to Blacklist Admin";
$locale['403'] = "Return to Admin Index";
$locale['404'] = "Please enter Blacklist IP or Blacklist Email";
$locale['405'] = "Blacklist email address is not valid email.";
$locale['406'] = "Blacklist";
// Add/Edit Blacklist Titles
$locale['420'] = "Blacklist User";
$locale['421'] = "Edit blacklisted user";
// Add/Edit blacklist form
$locale['440'] = "Entering an IP address will prevent a user whose IP address matches the entry from visiting this site.
You can enter a full IP, e.g. <em>123.45.67.89.</em>, or a partial IP, e.g. <em>123.45.67</em> or <em>123.45</em>.
Please note: IPv6 addresses are converted to their full length form on this site, 
e.g. <em>ABCD:1234:5:6:7:8:9:FF</em> will be shown as <em>ABCD:1234:0005:0006:0007:0008:0009:00FF</em>.
Mixed IP addresses (those contain both IPv6 and IPv4 part) will not be checked for partial match.
<br /><br />
Entering an email address will prevent members from registering using that address.
You can enter a full email address, e.g. <em>foo@bar.com</em>, or an email domain, e.g. <em>bar.com</em>.<br /><br />

% - matches any string.<br /><br />

%.%.%.%@domain.tld bans any address that contains at least 3 dots.<br />
%+%@domain.tld bans any address that contains at least one plus sign.<br />
%@domain.tld bans any address from domain.tld<br />
%.domain.tld bans all subdomains of domain.tld<br />
%payday% bans any address that contains the word \"payday\" which was very often on sites.<br />
domain.tld is an alias of %@domain.tld to make it compatible with rules defined in v7.<br />";

$locale['441'] = "Blacklist IP address: <strong>or</strong>";
$locale['442'] = "Blacklist email address:";
$locale['443'] = "Blacklist reason";
$locale['444'] = "Blacklist user";
// Current blacklisted users
$locale['460'] = "Blacklisted users";
$locale['461'] = "Blacklisted info";
$locale['462'] = "Options";
$locale['463'] = "Edit";
$locale['464'] = "Delete";
$locale['465'] = "The blacklist is currently empty.";
$locale['466'] = "N/A";
$locale['467'] = "Admin";
$locale['468'] = "Date";
