<?php
$locale['BLS_000'] = "Blacklist"; //406
//Blacklist message
$locale['BLS_010'] = "Invalid E-mail or IP address.";
$locale['BLS_011'] = "Adding users to blacklist.";
$locale['BLS_012'] = "User Modified blacklist.";
$locale['BLS_013'] = "User deleted from Blacklist"; //401
$locale['BLS_014'] = "Are you sure you want to delete this entry?";
$locale['BLS_015'] = "The blacklist is currently empty."; //465
$locale['BLS_016'] = "Blacklist email address is not valid email."; //405

$locale['BLS_020'] = "Blacklist User"; //420
$locale['BLS_021'] = "Edit blacklisted user"; //421
$locale['BLS_022'] = "Add blacklisted user";
$locale['BLS_023'] = "Currently displaying %d of %d total Blacklist entries.";

$locale['BLS_030'] = "Blacklisted info"; //461
$locale['BLS_031'] = "Admin"; //467
$locale['BLS_032'] = "Date";  //468
$locale['BLS_033'] = "Options"; //462
$locale['BLS_034'] = "Blacklist IP address: [STRONG]or[/STRONG]"; //441
$locale['BLS_035'] = "Blacklist email address"; //442
$locale['BLS_036'] = "Blacklist reason"; //443
$locale['BLS_037'] = "Blacklist user";  //444
$locale['BLS_038'] = "Update";
$locale['BLS_039'] = "Select All";

$locale['BLS_MS'] = "Entering an IP address will prevent a user whose IP address matches the entry from visiting this site.
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
%.domain.tld bans all sub-domains of domain.tld<br />
%payday% bans any address that contains the word \"payday\" which was very often on sites.<br />
domain.tld is an alias of %@domain.tld to make it compatible with rules defined in v7.<br />"; //440
