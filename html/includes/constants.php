<?php
// PHP file defining a bunch of global constants.

define("UID_COOKIE", "agnph_uid");
define("SALT_COOKIE", "agnph_salt");
define("SECONDS_IN_DAY", 60 * 60 * 24);
define("COOKIE_DURATION", 30 * SECONDS_IN_DAY);  // 30 days.
define("MAX_FILE_SIZE", 50 * 1024 * 1024);  // 50 MB.

// Site data tables.
define("SITE_NAV_TABLE", "nav_links");

// User content data tables.
define("USER_TABLE", "user");
define("FORUMS_LOBBY_TABLE", "forums_lobbies");
define("FORUMS_POST_TABLE", "forums_posts");
define("FORUMS_USER_PREF_TABLE", "forums_user");
define("FORUMS_UNREAD_POST_TABLE", "forums_unread_post");

// User Settings Defaults.
define("DEFAULT_SKIN", "agnph");
define("DEFAULT_THREADS_PER_PAGE", 5);
define("DEFAULT_POSTS_PER_PAGE", 5);
define("DEFAULT_PAGE_ITERATOR_SIZE", 2);  // 2 => 1 ... 5 6 [7] 8 9 ... 12
define("DEFAULT_ALLOWED_TAGS", "a[href],p[style],span[style],b,u,center,hr,br,");

?>