<?php
// PHP file defining a bunch of global constants.

define("UID_COOKIE", "agnph_uid");
define("SALT_COOKIE", "agnph_salt");
define("SECONDS_IN_DAY", 60 * 60 * 24);
define("COOKIE_DURATION", 30 * SECONDS_IN_DAY);  // 30 days.

// Site data tables.
define("SITE_NAV_TABLE", "nav_links");

// User content data tables.
define("USER_TABLE", "user");
define("FORUMS_LOBBY_TABLE", "forums_lobbies");
define("FORUMS_THREAD_TABLE", "forums_threads");
define("FORUMS_POST_TABLE", "forums_posts");
define("FORUMS_USER_PREF_TABLE", "forums_user");

// User Settings Defaults.
define("DEFAULT_SKIN", "agnph");
define("DEFAULT_THREADS_PER_PAGE", 5);
define("DEFAULT_POSTS_PER_PAGE", 10);
define("DEFAULT_PAGE_ITERATOR_SIZE", 2);  // 2 => 1 ... 5 6 [7] 8 9 ... 12

?>