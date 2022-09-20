<?php
// Holds blacklist of URLs for guest visits.
//
// Add URLs and UserAgents here that are suspected to be bots.

$BLACKLISTED_VISIT_URL_REGEXES = array(
    "^/gallery/post/show/108130/$",
    "^/gallery/post/.*(\\\\?|&)(md5|name|post_id|tags|title|user_id|width|height)(=|%3D).*$",
    "^/gallery/post/\\\\?[0-9]+$",
    "^/\\\\?.*$",
    "^/.*((select|\\\\*|from).*){2,}$",
);

$BLACKLISTED_USER_AGENT_REGEXES = array(
    preg_quote("Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.67 Safari/537.36"),
    ".*facebook.*",
    ".*[pP]ython.*",
    ".*[jJ]ava.*",
    ".*[hH][tT][tT][pP].*",
    "Cloud mapping experiment.*",
    ".*[pP]roxy.*",
    ".*AppEngine-Google.*",
    ".*bash.*",
);
?>