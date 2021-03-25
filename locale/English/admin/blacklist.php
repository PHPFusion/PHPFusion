<?php
$locale['BLS_000'] = "Blacklist";
//Blacklist message
$locale['BLS_010'] = "Email or IP address cannot be empty.";
$locale['BLS_011'] = "Entry has been added.";
$locale['BLS_012'] = "Entry has been modified.";
$locale['BLS_013'] = "Entry has been removed.";
$locale['BLS_014'] = "Are you sure you want to delete this entry?";
$locale['BLS_015'] = "The blacklist is currently empty.";
$locale['BLS_016'] = "Email address is not valid email.";

$locale['BLS_020'] = "Blacklist User";
$locale['BLS_021'] = "Edit blacklisted user";
$locale['BLS_022'] = "Add blacklisted user";
$locale['BLS_023'] = "Currently displaying %d of %d total Blacklist entries.";

$locale['BLS_030'] = "Blacklisted info";
$locale['BLS_031'] = "Admin";
$locale['BLS_032'] = "Date";
$locale['BLS_033'] = "Options";
$locale['BLS_034'] = "Blacklist IP address";
$locale['BLS_035'] = "Blacklist email address";
$locale['BLS_036'] = "Blacklist reason";
$locale['BLS_037'] = "Blacklist user";
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
domain.tld is an alias of %@domain.tld to make it compatible with rules defined in v7.<br />";
