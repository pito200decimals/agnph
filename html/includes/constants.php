<?php
// PHP file defining a bunch of global constants.

if (!defined("DEBUG")) {
    define("DEBUG", false);
}

define("UID_COOKIE", "agnph_uid");
define("SALT_COOKIE", "agnph_salt");
define("SECONDS_IN_DAY", 60 * 60 * 24);
define("COOKIE_DURATION", 30 * SECONDS_IN_DAY);  // 30 days.
define("MAX_FILE_SIZE", 50 * 1024 * 1024);  // 50 MB.

// Site data tables.
define("SITE_NAV_TABLE", "nav_links");
define("SITE_TAG_ALIAS_TABLE", "tag_aliases");
define("SITE_LOGGING_TABLE", "action_log");
define("SITE_TEXT_TABLE", "site_text");

// User content data tables.
define("USER_TABLE", "user");
define("FORUMS_USER_PREF_TABLE", "forums_user");
define("GALLERY_USER_PREF_TABLE", "gallery_user");
define("GALLERY_USER_FAVORITES_TABLE", "gallery_user_fav");
define("FICS_USER_PREF_TABLE", "fics_user");
// define("FICS_USER_FAVORITES_TABLE", "gallery_user_fav");
define("USER_MAILBOX_TABLE", "user_mail");

// Forums tables.
define("FORUMS_LOBBY_TABLE", "forums_lobbies");
define("FORUMS_POST_TABLE", "forums_posts");
define("FORUMS_UNREAD_POST_TABLE", "forums_unread_post");

// Gallery tables.
define("GALLERY_POST_TABLE", "gallery_posts");
define("GALLERY_TAG_TABLE", "gallery_tags");
define("GALLERY_POST_TAG_TABLE", "gallery_post_tag");
define("GALLERY_POST_TAG_HISTORY_TABLE", "gallery_tag_history");
define("GALLERY_POOLS_TABLE", "gallery_pools");
define("GALLERY_COMMENT_TABLE", "gallery_comments");

// Fics tables.
define("FICS_STORY_TABLE", "fics_stories");
define("FICS_CHAPTER_TABLE", "fics_chapters");
define("FICS_STORY_TAG_TABLE", "fics_story_tag");
define("FICS_TAG_TABLE", "fics_tags");
define("FICS_REVIEW_TABLE", "fics_reviews");

// User Settings Defaults.
define("DEFAULT_SKIN", "agnph");
define("DEFAULT_FORUM_THREADS_PER_PAGE", 25);
define("DEFAULT_FORUM_POSTS_PER_PAGE", 10);
define("DEFAULT_PAGE_ITERATOR_SIZE", 2);  // 2 => 1 ... 5 6 [7] 8 9 ... 12
define("DEFAULT_ALLOWED_TAGS", "a[href],p[style],span[style],b,u,i,strong,em,ol,ul,li,center,hr,br,div,pre,small");  // For Forums, Fics and Bios.
define("DEFAULT_GALLERY_POSTS_PER_PAGE", 45);  // Customizable.
define("DEFAULT_GALLERY_PAGE_ITERATOR_SIZE", 2);
define("MAX_GALLERY_SEARCH_TERMS", 6);
define("MAX_FICS_SEARCH_TERMS", 6);
define("GALLERY_LIST_ITEMS_PER_PAGE", 50);
define("DEFAULT_GALLERY_COMMENTS_PER_PAGE", 10);
define("DEFAULT_FICS_STORIES_PER_PAGE", 15);  // Customizable.
define("DEFAULT_FICS_COMMENTS_PER_PAGE", 10);
define("FICS_LIST_ITEMS_PER_PAGE", 50);

// General constants.
$GALLERY_TAG_TYPES = array(
    "A" => "Artist",
    "C" => "Character",
    "D" => "Copyright",
    "G" => "General",
    "S" => "Species");
$FICS_TAG_TYPES = array(
    "C" => "Category",
    "S" => "Species",
    "W" => "Warning",
    "H" => "Character",
    "R" => "Series",
    "G" => "General");
define("MAX_IMAGE_THUMB_SIZE", 150);
define("MAX_IMAGE_PREVIEW_SIZE", 1200);
define("MAX_TAG_NAME_LENGTH", 32);
define("MAX_POOL_NAME_LENGTH", 128);
define("MIN_POOL_PREFIX_LENGTH", 3);  // For ajax add-to-pool search.
define("MAX_GALLERY_POST_FLAG_REASON_LENGTH", 64);
define("FORUMS_DATE_FORMAT", "Y-m-d H:i:s");
define("GALLERY_DATE_FORMAT", "Y-m-d");
define("FICS_DATE_FORMAT", "Y-m-d");
define("PROFILE_DATE_FORMAT", "Y-m-d");
define("PROFILE_DATE_TIME_FORMAT", "Y-m-d H:i:s");
define("PROFILE_DOB_FORMAT", "F d Y");
define("MIN_COMMENT_STRING_SIZE", 10);
define("MIN_FICS_TITLE_SUMMARY_SEARCH_STRING_SIZE", 3);
define("MAX_FORUMS_SIGNATURE_LENGTH", 256);
define("MAX_FORUMS_THREADS_PER_PAGE", 100);
define("MAX_FORUMS_POSTS_PER_PAGE", 50);
define("MAX_GALLERY_POSTS_PER_PAGE", 100);
define("MAX_FICS_POSTS_PER_PAGE", 100);
define("MAX_GALLERY_BLACKLIST_TAGS", 50);
define("MAX_FICS_BLACKLIST_TAGS", 50);
define("FICS_NOT_FEATURED", "D");  // Also present in templates: editstory.tpl
define("GALLERY_PROFILE_SHOW_NUM_UPLOADS", 6);
define("GALLERY_PROFILE_SHOW_NUM_FAVORITES", 6);
define("MAX_PM_LENGTH", 4096);
define("MIN_DISPLAY_NAME_LENGTH", 3);
define("INBOX_ITEMS_PER_PAGE", 50);
define("MIN_USER_LOOKUP_PREFIX_LENGTH", 1);  // For ajax PM user lookup.

define("MIN_USERNAME_LENGTH", 3);  // Also present in register.tpl
define("MAX_USERNAME_LENGTH", 24);  // Also present in register.tpl
define("MIN_PASSWORD_LENGTH", 4);  // Also present in register.tpl

define("REGISTER_ACCOUNT_EXPIRE_TIME_READABLE_STRING", "24 hours");
define("REGISTER_ACCOUNT_EXPIRE_TIME", "24 HOUR");  // Time after registration that email link expires.

?>