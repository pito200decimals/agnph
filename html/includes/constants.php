<?php
// PHP file defining a bunch of global constants.

if (!defined("DEBUG")) {
    define("DEBUG", false);
}

// Login and SQL constants.
define("UID_COOKIE", "agnph_uid");
define("SALT_COOKIE", "agnph_salt");
define("SECONDS_IN_DAY", 60 * 60 * 24);
define("COOKIE_DURATION", 30 * SECONDS_IN_DAY);  // 30 days since last visit.
define("MAX_FILE_SIZE", 50 * 1024 * 1024);  // 50 MB.
define("SQL_TABLE_PREFIX", "v2_");

// Site data tables.
define("SITE_NAV_TABLE", SQL_TABLE_PREFIX."nav_links");
define("SITE_TAG_ALIAS_TABLE", SQL_TABLE_PREFIX."tag_aliases");
define("SITE_LOGGING_TABLE", SQL_TABLE_PREFIX."action_log");
define("SITE_SETTINGS_TABLE", SQL_TABLE_PREFIX."site_settings");
define("SECURITY_EMAIL_TABLE", SQL_TABLE_PREFIX."account_recovery");
define("USER_VISIT_TABLE", SQL_TABLE_PREFIX."user_visit");

// User content data tables.
define("USER_TABLE", SQL_TABLE_PREFIX."user");
define("FORUMS_USER_PREF_TABLE", SQL_TABLE_PREFIX."forums_user");
define("GALLERY_USER_PREF_TABLE", SQL_TABLE_PREFIX."gallery_user");
define("GALLERY_USER_FAVORITES_TABLE", SQL_TABLE_PREFIX."gallery_user_fav");
define("FICS_USER_PREF_TABLE", SQL_TABLE_PREFIX."fics_user");
define("FICS_USER_FAVORITES_TABLE", SQL_TABLE_PREFIX."fics_user_fav");
define("USER_MAILBOX_TABLE", SQL_TABLE_PREFIX."user_mail");
define("OEKAKI_USER_PREF_TABLE", SQL_TABLE_PREFIX."oekaki_user");

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

// Oekaki tables.
define("OEKAKI_POST_TABLE", SQL_TABLE_PREFIX."oekaki_posts");
define("OEKAKI_LIVESTREAM_TABLE", SQL_TABLE_PREFIX."oekaki_livestream");

////////////////////////////////////////////////////////////////////
// Settings Defaults and Limits (Embedded in SQL table defaults). //
// These should not change once SQL is set up.                    //
////////////////////////////////////////////////////////////////////
// User related.
define("MIN_USERNAME_LENGTH", 3);  // Also present in register.tpl (Not in SQL).
define("MAX_USERNAME_LENGTH", 48);  // Also present in register.tpl (should be 24, but need 48 for imported accounts).
define("MIN_DISPLAY_NAME_LENGTH", 3);  // Not in SQL.
define("MAX_DISPLAY_NAME_LENGTH", 24);
define("MAX_USER_EMAIL_LENGTH", 64);
define("MAX_BAN_REASON_LENGTH", 256);
define("MAX_USER_TITLE_LENGTH", 64);
define("MAX_USER_LOCATION_LENGTH", 64);
define("MAX_USER_SPECIES_LENGTH", 32);
define("DEFAULT_SKIN", "Retro");
define("DEFAULT_SKIN_SETTING", "default");
// Forums related.
define("MAX_FORUMS_BOARD_TITLE_LENGTH", 64);
define("MAX_FORUMS_BOARD_DESCRIPTION_LENGTH", 512);
define("MAX_FORUMS_POST_TITLE_LENGTH", 256);
define("MAX_FORUMS_POST_LENGTH", 131072);
define("MAX_FORUMS_SIGNATURE_LENGTH", 1024);
define("DEFAULT_FORUM_THREADS_PER_PAGE", 25);
define("DEFAULT_FORUM_POSTS_PER_PAGE", 10);
define("MAX_GALLERY_POST_FLAG_REASON_LENGTH", 128);
// Gallery related.
define("DEFAULT_GALLERY_POSTS_PER_PAGE", 45);
define("MAX_GALLERY_POST_DESCRIPTION_LENGTH", 4096);
define("MIN_GALLERY_POOL_NAME_LENGTH", 3);
define("MAX_GALLERY_POOL_NAME_LENGTH", 128);
define("MAX_GALLERY_POOL_DESCRIPTION_LENGTH", 512);
define("MAX_GALLERY_COMMENT_LENGTH", 4096);
// Fics related.
define("MAX_FICS_STORY_TITLE_LENGTH", 256);
define("MAX_FICS_STORY_SUMMARY_LENGTH", 4096);
define("MAX_FICS_STORY_NOTES_LENGTH", 1024);
define("MAX_FICS_CHAPTER_TITLE_LENGTH", 256);
define("MAX_FICS_CHAPTER_NOTES_LENGTH", 1024);
define("MAX_FICS_COMMENT_LENGTH", 4096);
define("DEFAULT_FICS_STORIES_PER_PAGE", 15);
define("FICS_RSS_NUM_ITEMS", 10);
// Oekaki related
define("DEFAULT_OEKAKI_POSTS_PER_PAGE", 25);
define("MAX_OEKAKI_POST_TITLE_LENGTH", 128);
define("MAX_OEKAKI_POST_TEXT_LENGTH", 1024);
// Other user constants.
define("MAX_PM_TITLE_LENGTH", 256);
define("MAX_PM_LENGTH", 4096);
define("MAX_KNOWN_IP_STRING_LENGTH", 512);
define("MAX_SKIN_STRING_LENGTH", 16);
// Tagging related.
define("MAX_TAG_NAME_LENGTH", 32);
define("MAX_TAG_NOTE_LENGTH", 1024);
define("MIN_TAG_COUNT_FOR_AUTOCOMPLETE", 5);
define("NUM_AUTOCOMPLETE_RESULTS", 3);
define("ADMIN_TAG_FETCH_LIMIT", 250);
// Logging related.
define("MAX_LOG_ACTION_STRING_LENGTH", 512);

///////////////////////////////////////
// Section for other site constants. //
///////////////////////////////////////
// Site constants (Both data and display-related)
define("NO_HTML_TAGS", "");
define("DEFAULT_ALLOWED_TAGS", "a[href],p[style|class],span[style|class],b,u,i,strong,em,ol,ul,li,center,hr,br,div[style|class],pre,small,blockquote,img[src|style|class|width|height|alt]");  // For comments, forums, fics, user bios.

// Site constants (data-related).
define("MIN_PASSWORD_LENGTH", 4);  // Also present in register.tpl
define("AVATAR_UPLOAD_EXTENSION", "png");
define("MAX_AVATAR_UPLOAD_DIMENSIONS", 200);
define("IMPORTED_ACCOUNT_USERNAME_PREFIX", "imported-");  // Normal usernames can't have hyphens.
define("ACCOUNT_IMPORTED_SQL_CONDITION", "(UserName LIKE '".IMPORTED_ACCOUNT_USERNAME_PREFIX."%')");
define("ACCOUNT_NOT_IMPORTED_SQL_CONDITION", "(NOT(".ACCOUNT_IMPORTED_SQL_CONDITION."))");
define("MIN_COMMENT_STRING_SIZE", 10);  // TODO: Enforce everywhere.
define("SITE_NEWS_SOURCE_BOARD_NAME_KEY", "SiteNewsBoardName");
define("FORUMS_NEWS_SOURCE_BOARD_NAME_KEY", "ForumsNewsBoardName");
define("GALLERY_NEWS_SOURCE_BOARD_NAME_KEY", "GalleryNewsBoardName");
define("FICS_NEWS_SOURCE_BOARD_NAME_KEY", "FicsNewsBoardName");
define("OEKAKI_NEWS_SOURCE_BOARD_NAME_KEY", "OekakiNewsBoardName");
define("GALLERY_THUMB_FILE_EXTENSION", "png");  // Don't change once gallery starts indexing.
define("MAX_GALLERY_IMAGE_THUMB_SIZE", 300);
define("MAX_GALLERY_IMAGE_PREVIEW_SIZE", 1200);
define("INITIAL_GALLERY_UPLOAD_LIMIT", 10);
define("GALLERY_ADMIN_TAG_ALIAS_CHANGE_LIMIT", 1000);  // Max # of posts to edit when adding an alias.
define("GALLERY_MAX_MASS_TAG_EDIT_COUNT", 1000);  // Max # of posts to edit when mass-editing.
define("MAX_GALLERY_SEARCH_TERMS", 6);
define("MAX_GALLERY_BLACKLIST_TAGS", 50);  // Max data input limit.
define("FICS_ADMIN_TAG_ALIAS_CHANGE_LIMIT", 1000);  // Max # of stories to edit when adding an alias.
define("MIN_FICS_TITLE_SUMMARY_SEARCH_STRING_SIZE", 3);  // Min char-count for token to search titles/summaries.
define("FICS_NOT_FEATURED", "D");  // Also present in templates: editstory.tpl
define("FICS_CHAPTER_MIN_WORD_COUNT_KEY", "FicsMinWordCount");
define("DEFAULT_FICS_CHAPTER_MIN_WORD_COUNT", 500);
define("FICS_WELCOME_MESSAGE_KEY", "FicsWelcomeMessage");
define("FICS_NUM_RANDOM_STORIES_KEY", "FicsNumRandStories");
define("FICS_NUM_RECENT_STORIES_KEY", "FicsNumRecentStories");
define("FICS_EVENTS_LIST_KEY", "FicsEvents");
define("FICS_MAX_NUM_RANDOM_STORIES", 5);
define("FICS_MAX_NUM_RECENT_STORIES", 5);
define("DEFAULT_FICS_NUM_RANDOM_STORIES", 1);
define("DEFAULT_FICS_NUM_RECENT_STORIES", 3);
define("FICS_MAX_NUM_COAUTHORS", 3);
define("MAX_FICS_SEARCH_TERMS", 6);
define("MAX_FICS_BLACKLIST_TAGS", 50);  // Max data input limit.
define("UNTHREADED_CONVERSATION_LINK_TIME", 7 * 24 * 60 * 60);
define("MAX_OEKAKI_SAVE_SLOTS", 3);  // Also in oekaki app constants.
define("OEKAKI_THUMB_FILE_EXTENSION", "png");  // Don't change once oekaki is launched.
define("MAX_OEKAKI_IMAGE_THUMB_SIZE", 300);
define("OEKAKI_THUMB_FILE_NAME", "thumb.".OEKAKI_THUMB_FILE_EXTENSION);
define("OEKAKI_METADATA_FILE_NAME", "data.txt");
define("MAX_OEKAKI_IMAGE_SIZE", 1200);
define("MAX_OEKAKI_NUM_LAYERS", 24);

// Site constants (display-related).
define("DEFAULT_PAGE_ITERATOR_SIZE", 2);  // 2 => 1 ... 5 6 [7] 8 9 ... 12
define("DEFAULT_MOBILE_PAGE_ITERATOR_SIZE", 1);  // 1 => 1 ... 6 [7] 8 ... 12
define("BASE_SKIN", "agnph");  // Skin to default values to if skin omits files.
define("DEFAULT_AVATAR_PATH", "/images/default-avatar.png");
define("DEFAULT_DATE_FORMAT", "Y-m-d H:i:s");
define("NEWS_POST_DATE_FORMAT", "M j Y");
define("IRC_MIRROR_TIME_FORMAT", "H:i");
define("MAX_SITE_NEWS_POSTS_KEY", "MaxSiteNewsPosts");
define("DEFAULT_MAX_SITE_NEWS_POSTS", 5);
define("PROFILE_DATE_FORMAT", "M j Y");  // For register date.
define("PROFILE_DATE_TIME_FORMAT", "g:i A M j Y");  // For last visit time.
define("PROFILE_MAIL_DATE_FORMAT_SHORT", "g:i A");
define("PROFILE_MAIL_DATE_FORMAT_LONG", "M j g:i A");
define("PROFILE_MAIL_DATE_FORMAT_VERY_LONG", "M j Y");
define("PROFILE_DOB_FORMAT", "F j Y");
define("PROFILE_TIME_FORMAT", "g:i A M j, Y");  // For local time.
define("MAX_FORUMS_THREADS_PER_PAGE", 100);  // Max value for user-setting.
define("MAX_FORUMS_POSTS_PER_PAGE", 50);  // Max value for user-setting.
define("FORUMS_DATE_FORMAT", "M j, Y g:i A");
define("FORUMS_QUOTE_DATE_FORMAT", "M j, Y");
define("FORUMS_PROFILE_SHOW_NUM_RECENT_POSTS", 6);
define("MAX_GALLERY_POSTS_PER_PAGE", 100);  // Max value for user-setting.
define("GALLERY_LIST_ITEMS_PER_PAGE", 50);
define("GALLERY_COMMENTS_PER_PAGE", 10);
define("GALLERY_DATE_FORMAT", "M j Y");
define("GALLERY_DATE_LONG_FORMAT", "g:i A M j Y");
define("GALLERY_PROFILE_SHOW_NUM_UPLOADS", 6);
define("GALLERY_PROFILE_SHOW_NUM_FAVORITES", 6);
define("GALLERY_NUM_SUGGESTED_SPELLING_TAGS", 3);
define("MAX_FICS_POSTS_PER_PAGE", 100);  // Max value for user-setting.
define("FICS_LIST_ITEMS_PER_PAGE", 50);
define("FICS_COMMENTS_PER_PAGE", 10);
define("FICS_DATE_FORMAT", "M j Y");
define("FICS_PROFILE_SHOW_NUM_STORIES", 3);
define("FICS_PROFILE_SHOW_NUM_FAVORITES", 3);
define("FICS_MAX_FEATURED_STORIES", 5);
define("MAX_FICS_SHORT_SUMMARY_LEGNTH", 100);
define("FICS_MAX_NEWS_POSTS_KEY", "FicsMaxNewsPosts");
define("OEKAKI_PROFILE_SHOW_NUM_POSTS", 6);
define("DEFAULT_FICS_MAX_NEWS_POSTS", 5);
define("USERS_LIST_ITEMS_PER_PAGE", 50);
define("INBOX_ITEMS_PER_PAGE", 50);
define("USERLIST_DATE_FORMAT", "M j Y");
define("ADMIN_LOG_ENTRIES_PER_PAGE", 50);
define("IMPORT_USER_WELCOME_PM_KEY", "ImportUserLoginPMKey");

// Other default constant values.
define("SITE_DOMAIN", "https://agn.ph");  // Used for post-image-search urls, email auth.
define("VERSION", "2.0");
define("SITE_WELCOME_MESSAGE_KEY", "SiteWelcomeMessage");
define("REGISTER_DISCLAIMER_KEY", "RegisterDisclaimer");
define("LOGIN_MESSAGE_KEY", "LoginMessage");
define("SHORT_BAN_DURATION_KEY", "UserShortBanDuration");
define("MAINTENANCE_MODE_KEY", "MaintenanceMode");
define("OEKAKI_LIVESTREAM_IDS_KEY", "LivestreamUserIds");
// Site security-related values.
define("DEFAULT_SHORT_BAN_DURATION", 7 * 24 * 60 * 60);  // 7 days, modifiable site setting.
define("DISPLAY_NAME_CHANGE_TIME_LIMIT", 24*60*60);  // Once a day.
define("DISPLAY_NAME_CHANGE_TIME_LIMIT_STR", "24 hours");
define("SECURITY_EMAIL_TABLE_SQL_CHECK_DELETE_INTERVAL", "'0:15' HOUR_MINUTE");  // Check every 15 minutes.
define("REGISTER_ACCOUNT_TIMESTAMP_DURATION", 3*24*60*60);  // Set to expire after 72 hours.
define("REGISTER_ACCOUNT_HUMAN_READABLE_STRING", "72 hours");
define("REGISTER_ACCOUNT_SQL_EVENT_DURATION", "72 HOUR");  // Delete user entry after 72 hours.
define("DEFAULT_EMAIL_EXPIRE_TIMESTAMP_DURATION", 60*60);  // Set to expire after 1 hour.
define("DEFAULT_EMAIL_EXPIRE_HUMAN_READABLE_STRING", "1 hour");
define("ALLOW_GALLERY_EDITS_AFTER_REGISTRATION_DEADLINE", 7 * 24 * 60 * 60);  // 7 days.
define("GALLERY_EDITS_AFTER_REGISTRATION_DEADLINE_STRING", "7 days");
define("REFRESH_ONLINE_TIMEOUT", 5 * 60);  // 5 minutes.
define("CONSIDERED_ONLINE_DURATION", 15 * 60);  // 15 minutes.
define("AGE_GATE_PATH", "confirm_age");

$GALLERY_TAG_TYPES = array(  // Note: Present in edit-post template and CSS.
    "A" => "Artist",
    "C" => "Character",
    "B" => "Copyright",
    "D" => "Species",
    "M" => "General");
$FICS_TAG_TYPES = array(  // Note: Present in edit-story template and CSS.
    "A" => "Category",
    "B" => "Series",
    "C" => "Character",
    "D" => "Species",
    "M" => "General",
    "Z" => "Warning");
$AGE_GATE_SECTIONS = array(
    "forums",
    "gallery",
    "fics",
    "oekaki",
    "user",
    "about/staff",
);
define("HIDE_IMPORTED_ACCOUNTS_FROM_USER_LIST", false);

?>