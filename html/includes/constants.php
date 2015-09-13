<?php
// PHP file defining a bunch of global constants.

if (!defined("DEBUG")) {
    define("DEBUG", false);
}

define("UID_COOKIE", "agnph_uid");
define("SALT_COOKIE", "agnph_salt");
define("SECONDS_IN_DAY", 60 * 60 * 24);
define("COOKIE_DURATION", 30 * SECONDS_IN_DAY);  // 30 days since last visit.
define("MAX_FILE_SIZE", 50 * 1024 * 1024);  // 50 MB.
define("SQL_TABLE_PREFIX", "");

// Site data tables.
define("SITE_NAV_TABLE", SQL_TABLE_PREFIX."nav_links");
define("SITE_TAG_ALIAS_TABLE", SQL_TABLE_PREFIX."tag_aliases");
define("SITE_LOGGING_TABLE", SQL_TABLE_PREFIX."action_log");
define("SITE_SETTINGS_TABLE", SQL_TABLE_PREFIX."site_settings");
define("SECURITY_EMAIL_TABLE", SQL_TABLE_PREFIX."account_recovery");

// User content data tables.
define("USER_TABLE", SQL_TABLE_PREFIX."user");
define("FORUMS_USER_PREF_TABLE", SQL_TABLE_PREFIX."forums_user");
define("GALLERY_USER_PREF_TABLE", SQL_TABLE_PREFIX."gallery_user");
define("GALLERY_USER_FAVORITES_TABLE", SQL_TABLE_PREFIX."gallery_user_fav");
define("FICS_USER_PREF_TABLE", SQL_TABLE_PREFIX."fics_user");
define("FICS_USER_FAVORITES_TABLE", SQL_TABLE_PREFIX."fics_user_fav");
define("USER_MAILBOX_TABLE", SQL_TABLE_PREFIX."user_mail");

// Forums tables.
define("FORUMS_BOARD_TABLE", SQL_TABLE_PREFIX."forums_boards");
define("FORUMS_POST_TABLE", SQL_TABLE_PREFIX."forums_posts");
define("FORUMS_UNREAD_POST_TABLE", SQL_TABLE_PREFIX."forums_unread_post");

// Gallery tables.
define("GALLERY_POST_TABLE", SQL_TABLE_PREFIX."gallery_posts");
define("GALLERY_TAG_TABLE", SQL_TABLE_PREFIX."gallery_tags");
define("GALLERY_POST_TAG_TABLE", SQL_TABLE_PREFIX."gallery_post_tag");
define("GALLERY_POST_TAG_HISTORY_TABLE", SQL_TABLE_PREFIX."gallery_tag_history");
define("GALLERY_POOLS_TABLE", SQL_TABLE_PREFIX."gallery_pools");
define("GALLERY_COMMENT_TABLE", SQL_TABLE_PREFIX."gallery_comments");
define("GALLERY_TAG_ALIAS_TABLE", SQL_TABLE_PREFIX."gallery_tag_aliases");
define("GALLERY_TAG_IMPLICATION_TABLE", SQL_TABLE_PREFIX."gallery_tag_implications");
define("GALLERY_DESC_HISTORY_TABLE", SQL_TABLE_PREFIX."gallery_desc_history");

// Fics tables.
define("FICS_STORY_TABLE", SQL_TABLE_PREFIX."fics_stories");
define("FICS_CHAPTER_TABLE", SQL_TABLE_PREFIX."fics_chapters");
define("FICS_STORY_TAG_TABLE", SQL_TABLE_PREFIX."fics_story_tag");
define("FICS_TAG_TABLE", SQL_TABLE_PREFIX."fics_tags");
define("FICS_REVIEW_TABLE", SQL_TABLE_PREFIX."fics_reviews");
define("FICS_TAG_ALIAS_TABLE", SQL_TABLE_PREFIX."fics_tag_aliases");
define("FICS_TAG_IMPLICATION_TABLE", SQL_TABLE_PREFIX."fics_tag_implications");

// User Settings Defaults.
define("DEFAULT_SKIN", "agnph");
define("DEFAULT_FORUM_THREADS_PER_PAGE", 25);
define("DEFAULT_FORUM_POSTS_PER_PAGE", 10);
define("DEFAULT_PAGE_ITERATOR_SIZE", 2);  // 2 => 1 ... 5 6 [7] 8 9 ... 12
define("NO_HTML_TAGS", "");
define("DEFAULT_ALLOWED_TAGS", "a[href],p[style],span[style],b,u,i,strong,em,ol,ul,li,center,hr,br,div,pre,small");  // For Forums, Fics and Bios.
define("DEFAULT_GALLERY_POSTS_PER_PAGE", 45);  // Customizable.
define("DEFAULT_GALLERY_PAGE_ITERATOR_SIZE", 2);  // Used in gallery post index page.
define("MAX_GALLERY_SEARCH_TERMS", 6);
define("MAX_FICS_SEARCH_TERMS", 6);
define("GALLERY_LIST_ITEMS_PER_PAGE", 50);
define("DEFAULT_GALLERY_COMMENTS_PER_PAGE", 10);
define("DEFAULT_FICS_STORIES_PER_PAGE", 15);  // Customizable.
define("DEFAULT_FICS_COMMENTS_PER_PAGE", 10);
define("FICS_LIST_ITEMS_PER_PAGE", 50);
define("USERS_LIST_ITEMS_PER_PAGE", 50);
define("MAX_FORUMS_POST_LENGTH", 131072);

// General constants.
// Site-related
define("SITE_DOMAIN", "http://agnph.cloudapp.net");  // TODO: Change on live site.
define("VERSION", "0.9.1");
define("MIN_USERNAME_LENGTH", 3);  // Also present in register.tpl
define("MAX_USERNAME_LENGTH", 24);  // Also present in register.tpl
define("MIN_PASSWORD_LENGTH", 4);  // Also present in register.tpl
define("MAX_TAG_NAME_LENGTH", 32);
define("PROFILE_DATE_FORMAT", "M j Y");  // For register date.
define("PROFILE_DATE_TIME_FORMAT", "g:i A M j Y");  // For last visit time.
define("PROFILE_MAIL_DATE_FORMAT_SHORT", "g:i A");
define("PROFILE_MAIL_DATE_FORMAT_LONG", "M j g:i A");
define("PROFILE_MAIL_DATE_FORMAT_VERY_LONG", "M j Y");
define("PROFILE_DOB_FORMAT", "F j Y");
define("MIN_COMMENT_STRING_SIZE", 10);  // TODO: Enforce everywhere.
define("MAX_PM_LENGTH", 4096);
define("MIN_DISPLAY_NAME_LENGTH", 3);
define("MAX_DISPLAY_NAME_LENGTH", 24);
define("MIN_USER_NAME_LENGTH", 3);
define("MAX_USER_NAME_LENGTH", 24);
define("INBOX_ITEMS_PER_PAGE", 50);
define("MIN_USER_LOOKUP_PREFIX_LENGTH", 1);  // For ajax PM user lookup.
define("DISPLAY_NAME_CHANGE_TIME_LIMIT", 24*60*60);  // Once a day.
define("DISPLAY_NAME_CHANGE_TIME_LIMIT_STR", "24 hours");
define("DEFAULT_AVATAR_PATH", "/images/default-avatar.png");
define("AVATAR_UPLOAD_EXTENSION", "png");
define("MAX_AVATAR_UPLOAD_DIMENSIONS", 200);
define("MAX_EMAIL_LENGTH", 128);
define("BASE_SKIN", "agnph");
define("REGISTER_DISCLAIMER_KEY", "RegisterDisclaimer");
define("SHORT_BAN_DURATION_KEY", "UserShortBanDuration");
define("DEFAULT_SHORT_BAN_DURATION", 7 * 24 * 60 * 60);  // 7 days.
define("DEFAULT_DATE_FORMAT", "Y-m-d H:i:s");
define("IMPORTED_ACCOUNT_USERNAME_SUFFIX", "-imported");  // Normal usernames can't have hyphens.
define("MAINTENANCE_MODE_KEY", "MaintenanceMode");
// Site security-related timeouts.
define("REGISTER_ACCOUNT_HUMAN_READABLE_STRING", "24 hours");  // 24 hours.
define("REGISTER_ACCOUNT_SQL_EVENT_DURATION", "24 HOUR");
define("REGISTER_ACCOUNT_TIMESTAMP_DURATION", 24*60*60);
define("DEFAULT_EMAIL_EXPIRE_HUMAN_READABLE_STRING", "15 minutes");  // 15 minutes.
define("DEFAULT_EMAIL_EXPIRE_SQL_EVENT_DURATION", "0:15 HOUR_MINUTE");
define("DEFAULT_EMAIL_EXPIRE_TIMESTAMP_DURATION", 15*60);
define("ALLOW_GALLERY_EDITS_AFTER_REGISTRATION_DEADLINE", 7 * 24 * 60 * 60);  // 7 days.

// Forums-related
define("FORUMS_DATE_FORMAT", "M j, Y g:i A");
define("MAX_FORUMS_SIGNATURE_LENGTH", 1024);
define("MAX_FORUMS_THREADS_PER_PAGE", 100);
define("MAX_FORUMS_POSTS_PER_PAGE", 50);

// Gallery-related
$GALLERY_TAG_TYPES = array(
    "A" => "Artist",
    "C" => "Character",
    "D" => "Copyright",
    "G" => "General",
    "S" => "Species");
define("GALLERY_THUMB_FILE_EXTENSION", "png");  // Don't change once gallery starts indexing.
define("MAX_IMAGE_THUMB_SIZE", 200);
define("MAX_IMAGE_PREVIEW_SIZE", 1200);
define("MAX_POOL_NAME_LENGTH", 128);
define("MIN_POOL_PREFIX_LENGTH", 3);  // For ajax add-to-pool search.
define("MAX_GALLERY_POST_FLAG_REASON_LENGTH", 64);
define("GALLERY_DATE_FORMAT", "M j Y");
define("MAX_GALLERY_POSTS_PER_PAGE", 100);
define("MAX_GALLERY_BLACKLIST_TAGS", 50);
define("GALLERY_PROFILE_SHOW_NUM_UPLOADS", 6);
define("GALLERY_PROFILE_SHOW_NUM_FAVORITES", 6);
define("INITIAL_GALLERY_UPLOAD_LIMIT", 10);
define("GALLERY_ADMIN_TAG_ALIAS_CHANGE_LIMIT", 1000);  // Max # of posts to edit when adding an alias.
define("MAX_GALLERY_POST_DESCRIPTION_LENGTH", 1024);

// Fics-related
$FICS_TAG_TYPES = array(
    "C" => "Category",
    "S" => "Species",
    "W" => "Warning",
    "H" => "Character",
    "R" => "Series",
    "G" => "General");
define("FICS_DATE_FORMAT", "M j Y");
define("MIN_FICS_TITLE_SUMMARY_SEARCH_STRING_SIZE", 3);
define("MAX_FICS_POSTS_PER_PAGE", 100);
define("MAX_FICS_BLACKLIST_TAGS", 50);
define("FICS_NOT_FEATURED", "D");  // Also present in templates: editstory.tpl
// Fics site settings constants.
define("FICS_CHAPTER_MIN_WORD_COUNT_KEY", "FicsMinWordCount");
define("DEFAULT_FICS_CHAPTER_MIN_WORD_COUNT", 500);
define("FICS_WELCOME_MESSAGE_KEY", "FicsWelcomeMessage");
define("DEFAULT_FICS_WELCOME_MESSAGE", "");
define("FICS_PROFILE_SHOW_NUM_STORIES", 3);
define("FICS_PROFILE_SHOW_NUM_FAVORITES", 3);
define("FICS_MAX_NUM_COAUTHORS", 3);

// Account-related.
define("USERLIST_DATE_FORMAT", "Y-m-d");
define("REFRESH_ONLINE_TIMEOUT", 5 * 60);  // 5 minutes.
define("CONSIDERED_ONLINE_DURATION", 15 * 60);  // 15 minutes.





?>