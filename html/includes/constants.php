<?php
// PHP file defining a bunch of global constants.

if (!defined("DEBUG")) {
    define("DEBUG", true);
}

define("UID_COOKIE", "agnph_uid");
define("SALT_COOKIE", "agnph_salt");
define("SECONDS_IN_DAY", 60 * 60 * 24);
define("COOKIE_DURATION", 30 * SECONDS_IN_DAY);  // 30 days.
define("MAX_FILE_SIZE", 50 * 1024 * 1024);  // 50 MB.

// Site data tables.
define("SITE_NAV_TABLE", "nav_links");

// User content data tables.
define("USER_TABLE", "user");
define("FORUMS_USER_PREF_TABLE", "forums_user");
define("GALLERY_USER_PREF_TABLE", "gallery_user");
define("GALLERY_USER_FAVORITES_TABLE", "gallery_user_fav");

// Forums tables.
define("FORUMS_LOBBY_TABLE", "forums_lobbies");
define("FORUMS_POST_TABLE", "forums_posts");
define("FORUMS_UNREAD_POST_TABLE", "forums_unread_post");

// Gallery tables.
define("GALLERY_POST_TABLE", "gallery_posts");
define("GALLERY_TAG_TABLE", "gallery_tags");
define("GALLERY_POST_TAG_TABLE", "gallery_post_tag");
define("GALLERY_POST_TAG_HISTORY_TABLE", "gallery_tag_history");
define("GALLERY_TAG_ALIAS_TABLE", "gallery_tag_alias");
define("GALLERY_POOLS_TABLE", "gallery_pools");

// Fics tables.
define("FICS_STORY_TABLE", "fics_stories");
define("FICS_CHAPTER_TABLE", "fics_chapters");
define("FICS_STORY_TAG_TABLE", "fics_story_tag");
define("FICS_TAG_TABLE", "fics_tags");
define("FICS_REVIEW_TABLE", "fics_reviews");
define("FICS_SERIES_TABLE", "fics_series");

// User Settings Defaults.
define("DEFAULT_SKIN", "agnph");
define("DEFAULT_THREADS_PER_PAGE", 5);
define("DEFAULT_POSTS_PER_PAGE", 5);
define("DEFAULT_PAGE_ITERATOR_SIZE", 2);  // 2 => 1 ... 5 6 [7] 8 9 ... 12
define("DEFAULT_ALLOWED_TAGS", "a[href],p[style],span[style],b,u,i,strong,em,ol,ul,li,center,hr,br,div,pre,small");
define("DEFAULT_GALLERY_POSTS_PER_PAGE", 45);
define("DEFAULT_GALLERY_PAGE_ITERATOR_SIZE", 2);
define("MAX_GALLERY_SEARCH_TERMS", 6);
define("GALLERY_LIST_ITEMS_PER_PAGE", 50);

// General constants.
$GALLERY_TAG_TYPES = array(
    "A" => "Artist",
    "C" => "Character",
    "D" => "Copyright",
    "G" => "General",
    "S" => "Species");
define("MAX_IMAGE_THUMB_SIZE", 150);
define("MAX_IMAGE_PREVIEW_SIZE", 1200);
define("MAX_TAG_NAME_LENGTH", 32);
define("MAX_POOL_NAME_LENGTH", 32);
define("MIN_POOL_PREFIX_LENGTH", 3);
define("MAX_GALLERY_POST_FLAG_REASON_LENGTH", 64);

?>