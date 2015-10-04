<?php
// General setup script that will be used to set up the website.
// Should include any SQL database commands that are needed to create the
// site's database.

define("DEBUG", true);
define("SITE_ROOT", "../");
include_once(SITE_ROOT."includes/config.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."gallery/includes/functions.php");


////////////////////////////////
// Guidelines on field naming //
////////////////////////////////

// Use Camel-Case, with the first letter capitalized for all database column
// names. This will be the same when imported into the templates.
// However, any programmatically-specified fields in a template will be
// camel-case with the first letter NOT capitalized, to denote it is not a
// database column.

// If doesn't exist, is a no-op.
sql_query("DROP TABLE ".USER_TABLE.";");
sql_query("DROP TABLE ".USER_MAILBOX_TABLE.";");
sql_query("DROP TABLE ".SITE_LOGGING_TABLE.";");
sql_query("DROP TABLE ".SITE_SETTINGS_TABLE.";");
sql_query("DROP TABLE ".SECURITY_EMAIL_TABLE.";");
sql_query("DROP TABLE ".FORUMS_BOARD_TABLE.";");
sql_query("DROP TABLE ".FORUMS_POST_TABLE.";");
sql_query("DROP TABLE ".FORUMS_USER_PREF_TABLE.";");
sql_query("DROP TABLE ".FORUMS_UNREAD_POST_TABLE.";");
sql_query("DROP TABLE ".GALLERY_POST_TABLE.";");
sql_query("DROP TABLE ".GALLERY_TAG_TABLE.";");
sql_query("DROP TABLE ".GALLERY_POST_TAG_TABLE.";");
sql_query("DROP TABLE ".GALLERY_POST_TAG_HISTORY_TABLE.";");
sql_query("DROP TABLE ".GALLERY_DESC_HISTORY_TABLE.";");
sql_query("DROP TABLE ".GALLERY_COMMENT_TABLE.";");
sql_query("DROP TABLE ".GALLERY_USER_PREF_TABLE.";");
sql_query("DROP TABLE ".GALLERY_USER_FAVORITES_TABLE.";");
sql_query("DROP TABLE ".GALLERY_POOLS_TABLE.";");
sql_query("DROP TABLE ".GALLERY_TAG_ALIAS_TABLE.";");
sql_query("DROP TABLE ".GALLERY_TAG_IMPLICATION_TABLE.";");
sql_query("DROP TABLE ".FICS_STORY_TABLE.";");
sql_query("DROP TABLE ".FICS_CHAPTER_TABLE.";");
sql_query("DROP TABLE ".FICS_TAG_TABLE.";");
sql_query("DROP TABLE ".FICS_STORY_TAG_TABLE.";");
sql_query("DROP TABLE ".FICS_REVIEW_TABLE.";");
sql_query("DROP TABLE ".FICS_USER_PREF_TABLE.";");
sql_query("DROP TABLE ".FICS_USER_FAVORITES_TABLE.";");
sql_query("DROP TABLE ".FICS_TAG_ALIAS_TABLE.";");
sql_query("DROP TABLE ".FICS_TAG_IMPLICATION_TABLE.";");
// NOTE: If you add another user table, make sure to update account migration.
sql_query("DELETE FROM mysql.event");

// Main user data table. General information that is shared between sections.
do_or_die(sql_query(
    "CREATE TABLE ".USER_TABLE." (
        UserId INT(11) UNSIGNED AUTO_INCREMENT,".  // User's ID.
        // User/admin/signup-assigned values.
       "UserName VARCHAR(".MAX_USERNAME_LENGTH.") UNIQUE NOT NULL,
        DisplayName VARCHAR(".MAX_DISPLAY_NAME_LENGTH.") NOT NULL,
        Email VARCHAR(".MAX_USER_EMAIL_LENGTH.") NOT NULL,
        Password CHAR(32) NOT NULL,".  // Requires 32 for md5
       "Usermode INT(11) DEFAULT 0 NOT NULL,".  // -1=Banned, 0=Unactivated, 1=User. Unactivated users do not have anything besides this table entry.
       "Permissions VARCHAR(8) NOT NULL,".  // String of characters, A=Super Admin, R=Forums, G=Gallery, F=Fics, O=Oekaki, I=IRC, M=Minecraft
       "BanReason VARCHAR(".MAX_BAN_REASON_LENGTH.") NOT NULL,
        BanExpireTime INT(11) NOT NULL,".  // Timestamp when ban is lifted. -1 for infinite bans.
       "Title VARCHAR(".MAX_USER_TITLE_LENGTH.") NOT NULL,
        Location VARCHAR(".MAX_USER_LOCATION_LENGTH.") NOT NULL,
        AutoDetectTimezone TINYINT(1) DEFAULT 1 NOT NULL,
        Timezone FLOAT DEFAULT 0 NOT NULL,
        ShowLocalTime TINYINT(1) DEFAULT 1 NOT NULL,
        Species VARCHAR(".MAX_USER_SPECIES_LENGTH.") NOT NULL,
        DOB CHAR(10) NOT NULL,".  // Format: MM/DD/YYYY
       "ShowDOB TINYINT(1) DEFAULT 0,
        HideOnlineStatus TINYINT(1) DEFAULT 0,
        Gender CHAR(1) DEFAULT 'U',".  // U=Unspecified, M=Male, F=Female, O=Other
       "GroupMailboxThreads TINYINT(1) DEFAULT 1,
        AvatarPostId INT(11) DEFAULT -1,".  // Format: Post ID in gallery.
       "AvatarFname VARCHAR(16) DEFAULT '',".  // Format: "user$uid.$ext"
       "Skin VARCHAR(16) DEFAULT '".DEFAULT_SKIN."' NOT NULL,".
        // Code-assigned values.
       "JoinTime INT(11) NOT NULL,
        LastVisitTime INT(11) NOT NULL,
        DisplayNameChangeTime INT(11) DEFAULT 0 NOT NULL,
        RegisterIP VARCHAR(50) NOT NULL,".  // RegisterIP will be empty-string if the user was imported from the old site software.
       "KnownIPs VARCHAR(512) NOT NULL,".  // Allocate 45 + 1 characters for each IP address. Store the past 10 addresses comma-separated.
       "ImportForumsPassword VARCHAR(40) NOT NULL,
        ImportGalleryPassword VARCHAR(32) NOT NULL,
        ImportFicsPassword VARCHAR(32) NOT NULL,
        ImportOekakiPassword VARCHAR(32) NOT NULL,
        PRIMARY KEY(UserId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
do_or_die(sql_query("SET GLOBAL event_scheduler = ON;"));  // Turn on cleanup scheduler.
// User biography is stored in text files at /user/bio/{UserId}.txt

do_or_die(sql_query(
    "CREATE TABLE ".SITE_SETTINGS_TABLE." (
        Name VARCHAR(24) NOT NULL,
        Value TEXT(4096) NOT NULL,
        PRIMARY KEY(Name)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));

///////////////////
// Forums tables //
///////////////////

// Table specifying the structure of the general forum lobbies.
do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_BOARD_TABLE." (
        BoardId INT(11) UNSIGNED AUTO_INCREMENT,
        ParentId INT(11) NOT NULL,
        Name VARCHAR(".MAX_FORUMS_BOARD_TITLE_LENGTH.") NOT NULL,
        Description VARCHAR(".MAX_FORUMS_BOARD_DESCRIPTION_LENGTH.") NOT NULL,
        PrivateBoard TINYINT(1) DEFAULT 0 NOT NULL,
        Locked TINYINT(1) DEFAULT 0 NOT NULL,
        BoardSortOrder INT(11) DEFAULT 0 NOT NULL,
        NumPosts INT(11) NOT NULL,
        NumThreads INT(11) NOT NULL,
        LastPostId INT(11) NOT NULL,
        LastPostDate INT(11) NOT NULL,
        PRIMARY KEY(BoardId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// Post table. Contains both threads and posts. Threads are equal to the first post in the thread.
do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_POST_TABLE." (
        PostId INT(11) UNSIGNED AUTO_INCREMENT,
        UserId INT(11) NOT NULL,
        Title VARCHAR(256) NOT NULL,
        Text TEXT(".MAX_FORUMS_POST_LENGTH."),
        PostDate INT(11) NOT NULL,
        EditDate INT(11) DEFAULT 0 NOT NULL,
        ParentId INT(11) NOT NULL,
        IsThread TINYINT(1) DEFAULT 0 NOT NULL,".  // Properties below only belong to threads.
       "Replies INT(11) NOT NULL,
        Views INT(11) NOT NULL,
        LastPostDate INT(11) NOT NULL,
        Sticky TINYINT(1) DEFAULT 0 NOT NULL,
        Locked TINYINT(1) DEFAULT 0 NOT NULL,
        NewsPost TINYINT(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY(PostId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// User preferences specific to the forums section.
do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_USER_PREF_TABLE." (
        UserId INT(11) NOT NULL,
        Signature VARCHAR(".MAX_FORUMS_SIGNATURE_LENGTH.") DEFAULT '' NOT NULL,
        MaybeReadUpTo INT(11) DEFAULT 0 NOT NULL,".  // Index after last read post. This an all posts after are all unread.
       "ForumThreadsPerPage INT(11) DEFAULT ".DEFAULT_FORUM_THREADS_PER_PAGE.",
        ForumPostsPerPage INT(11) DEFAULT ".DEFAULT_FORUM_POSTS_PER_PAGE.",
        ForumsPermissions CHAR(1) DEFAULT 'N' NOT NULL,".  // R - Restricted user, N - Normal user, A - Admin
       "PRIMARY KEY(UserId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// Table containing rows of tuples of (UserId, PostId).
do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_UNREAD_POST_TABLE." (
        UserId INT(11) NOT NULL,
        PostId INT(11) NOT NULL,
        PRIMARY KEY(UserId, PostId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));


////////////////////
// Gallery tables //
////////////////////

// General information about a single post.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_POST_TABLE." (
        PostId INT(11) UNSIGNED AUTO_INCREMENT,
        Md5 CHAR(32) NOT NULL,
        Extension CHAR(4) NOT NULL,
        HasPreview TINYINT(1) DEFAULT 0,
        UploaderId INT(11) NOT NULL,
        DateUploaded INT(11) NOT NULL,
        Source VARCHAR(256) NOT NULL,
        Rating CHAR(1) DEFAULT 'q',
        Description TEXT(512) NOT NULL,
        ParentPostId INT(11) DEFAULT -1,
        ParentPoolId INT(11) DEFAULT -1,
        PoolItemOrder INT(11) NOT NULL,
        Score INT(11) DEFAULT 0,
        NumFavorites INT(11) DEFAULT 0,
        NumComments INT(11) DEFAULT 0,
        NumViews INT(11) DEFAULT 0,
        Width INT(11) NOT NULL,
        Height INT(11) NOT NULL,
        FileSize VARCHAR(8) NOT NULL,
        Status CHAR(1) DEFAULT 'P',".  // P for pending, A for approved, F for flagged for deletion, D for deleted (L for linked to source?)
       "FlaggerUserId INT(11) DEFAULT -1 NOT NULL,
        FlagReason VARCHAR(".MAX_GALLERY_POST_FLAG_REASON_LENGTH.") NOT NULL,
        PRIMARY KEY(PostId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// Tag Types: A=Artist, B=Copyright, C=Character, D=Species, M=General
CreateItemTagTables(GALLERY_TAG_TABLE, GALLERY_POST_TAG_TABLE, GALLERY_TAG_ALIAS_TABLE, GALLERY_TAG_IMPLICATION_TABLE, "PostId");
// History of tag edits for a given post.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_POST_TAG_HISTORY_TABLE." (
        Id INT(11) UNSIGNED AUTO_INCREMENT,".  // Just a unique ID, even though Timestamp should make it unique.
       "PostId INT(11) NOT NULL,
        Timestamp INT(11) NOT NULL,
        UserId INT(11) NOT NULL,
        TagsAdded TEXT(512) DEFAULT '',".  // Can hold ~120 tags.
       "TagsRemoved TEXT(512) DEFAULT '',".  // Can hold ~120 tags.
       "PropertiesChanged TEXT(512) DEFAULT '',".  // Can hold ~30 properties.
       "BatchId INT(11) DEFAULT 0,".  // Id for groups of related tag edits. 0 if unbatched. Not used, but may be helpful in the future.
       "PRIMARY KEY(Id, PostId, Timestamp)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_DESC_HISTORY_TABLE." (
        Id INT(11) UNSIGNED AUTO_INCREMENT,
        PostId INT(11) NOT NULL,
        Timestamp INT(11) NOT NULL,
        UserId INT(11) NOT NULL,
        Description TEXT(".MAX_GALLERY_POST_DESCRIPTION_LENGTH.") NOT NULL,
        PRIMARY KEY(Id, PostId, Timestamp)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// General information about pools.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_POOLS_TABLE." (
        PoolId INT(11) AUTO_INCREMENT,
        CreatorUserId INT(11) NOT NULL,
        Name VARCHAR(".MAX_GALLERY_POOL_NAME_LENGTH.") NOT NULL,
        Description TEXT(".MAX_GALLERY_POOL_DESCRIPTION_LENGTH.") NOT NULL,
        PRIMARY KEY(PoolId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// Table containing comments on posts. TODO: Score?
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_COMMENT_TABLE." (
        CommentId INT(11) UNSIGNED AUTO_INCREMENT,
        PostId INT(11) NOT NULL,
        UserId INT(11) NOT NULL,
        CommentDate INT(11) NOT NULL,
        CommentText TEXT(".MAX_GALLERY_COMMENT_LENGTH.") NOT NULL,
        PRIMARY KEY(CommentId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// User preferences for gallery section.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_USER_PREF_TABLE." (
        UserId INT(11) NOT NULL,
        UploadLimit INT(11) NOT NULL,
        ArtistTagId INT(11) NOT NULL,
        GalleryPermissions CHAR(1) DEFAULT 'N',".  // R - Restricted user, N - Normal user, C - Contributor, A - Admin
       "GalleryPostsPerPage INT(11) DEFAULT ".DEFAULT_GALLERY_POSTS_PER_PAGE.",
        GalleryTagBlacklist TEXT(512) NOT NULL,".  // Can hold ~120 tags.
       "NavigateGalleryPoolsWithKeyboard TINYINT(1) DEFAULT 0,
        PrivateGalleryFavorites TINYINT(1) DEFAULT 0,
        PlainGalleryTagging TINYINT(1) DEFAULT 0,
        PRIMARY KEY(UserId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// User favorites for gallery section.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_USER_FAVORITES_TABLE." (
        UserId INT(11) NOT NULL,
        PostId INT(11) NOT NULL,
        Timestamp INT(11) NOT NULL,
        PRIMARY KEY(UserId, PostId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));


/////////////////
// Fics tables //
/////////////////

// Fics table that contains all the story metadata.
do_or_die(sql_query(
    "CREATE TABLE ".FICS_STORY_TABLE." (
        StoryId INT(11) UNSIGNED AUTO_INCREMENT,
        AuthorUserId INT(11) NOT NULL,
        CoAuthors VARCHAR(24) NOT NULL,".  // Comma-separated ids. Limit 3 co-authors (+ 1 author).
       "DateCreated INT(11) NOT NULL,
        DateUpdated INT(11) NOT NULL,
        Title VARCHAR(".MAX_FICS_STORY_TITLE_LENGTH.") NOT NULL,
        Summary TEXT(".MAX_FICS_STORY_SUMMARY_LENGTH.") NOT NULL,
        Rating CHAR(11) NOT NULL,".  // G - G, P - PG, T - PG-13, R - R, X - XXX TODO: Ordering
       "ApprovalStatus CHAR(1) DEFAULT 'A',".  // P - Pending, A - Approved, D - Deleted (Pending not used).
       "Completed TINYINT(1) DEFAULT FALSE,
        Featured CHAR(1) DEFAULT '".FICS_NOT_FEATURED."',".  // D/F/f/G/g/S/s/Z/z (upper-case current, lower-case retired).
       "StoryNotes TEXT(".MAX_FICS_STORY_NOTES_LENGTH.") NOT NULL,
        ChapterCount INT(11) NOT NULL,
        WordCount INT(11) NOT NULL,
        Views INT(11) NOT NULL,
        TotalStars INT(11) NOT NULL,
        TotalRatings INT(11) NOT NULL,
        NumReviews INT(11) NOT NULL,
        PRIMARY KEY(StoryId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// Fics table that stores all the chapter metadata.
do_or_die(sql_query(
    "CREATE TABLE ".FICS_CHAPTER_TABLE." (
        ChapterId INT(11) UNSIGNED AUTO_INCREMENT,
        ParentStoryId INT(11) NOT NULL,
        AuthorUserId INT(11) NOT NULL,
        Title VARCHAR(".MAX_FICS_CHAPTER_TITLE_LENGTH.") NOT NULL,
        ChapterItemOrder INT(11) NOT NULL,
        ChapterNotes TEXT(".MAX_FICS_CHAPTER_NOTES_LENGTH.") NOT NULL,
        ChapterEndNotes TEXT(".MAX_FICS_CHAPTER_NOTES_LENGTH.") NOT NULL,
        WordCount INT(11) NOT NULL,
        Views INT(11) NOT NULL,
        TotalStars INT(11) NOT NULL,
        TotalRatings INT(11) NOT NULL,
        NumReviews INT(11) NOT NULL,
        ApprovalStatus CHAR(1) DEFAULT 'A',".  // P - Pending, A - Approved, D - Deleted (Pending not used).
       "PRIMARY KEY(ChapterId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));  // NOTE: WordCount, TotalStars and TotalRatings not implemented yet.
// Fics table that stores comments and reviews.
do_or_die(sql_query(
    "CREATE TABLE ".FICS_REVIEW_TABLE." (
        ReviewId INT(11) UNSIGNED AUTO_INCREMENT,
        StoryId INT(11) NOT NULL,
        ChapterId INT(11) NOT NULL,
        ReviewerUserId INT(11) NOT NULL,
        ReviewDate INT(11) NOT NULL,
        ReviewText TEXT(".MAX_FICS_COMMENT_LENGTH.") NOT NULL,
        AuthorResponseText TEXT(".MAX_FICS_COMMENT_LENGTH.") NOT NULL,
        ReviewScore INT(11) NOT NULL,
        IsReview TINYINT(1) NOT NULL,
        IsComment TINYINT(1) NOT NULL,
        PRIMARY KEY(ReviewId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// Tag Types: A - Category, B - Series, C - Character, D - Species, M - General, Z - Warning
CreateItemTagTables(FICS_TAG_TABLE, FICS_STORY_TAG_TABLE, FICS_TAG_ALIAS_TABLE, FICS_TAG_IMPLICATION_TABLE, "StoryId");
do_or_die(sql_query(
   "CREATE TABLE ".FICS_USER_PREF_TABLE." (
        UserId INT(11) NOT NULL,
        AuthorTagId INT(11) NOT NULL,
        FicsPermissions CHAR(1) DEFAULT 'N',".  // R - Restricted user, N - Normal user, A - Admin
       "FicsStoriesPerPage INT(11) DEFAULT ".DEFAULT_FICS_STORIES_PER_PAGE.",
        FicsTagBlacklist TEXT(512) NOT NULL,".  // Can hold ~120 tags.
       "PrivateFicsFavorites TINYINT(1) DEFAULT 0,
        PlainFicsTagging TINYINT(1) DEFAULT 0,
        PRIMARY KEY(UserId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// Table holding user story favorites.
do_or_die(sql_query(
    "CREATE TABLE ".FICS_USER_FAVORITES_TABLE." (
        UserId INT(11) NOT NULL,
        StoryId INT(11) NOT NULL,
        Timestamp INT(11) NOT NULL,
        PRIMARY KEY(UserId, StoryId)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
// TODO: Author following

// Table holding all user PM's.
do_or_die(sql_query(
    "CREATE TABLE ".USER_MAILBOX_TABLE." (
        Id INT(11) UNSIGNED AUTO_INCREMENT,
        SenderUserId INT(11) NOT NULL,
        RecipientUserId INT(11) NOT NULL,
        ParentMessageId INT(11) NOT NULL,
        Timestamp INT(11) NOT NULL,
        Status CHAR(1) DEFAULT 'U',".  // U - Unread, R - Read, D - Deleted
       "Title VARCHAR(".MAX_PM_TITLE_LENGTH.") NOT NULL,
        Content TEXT(".MAX_PM_LENGTH.") NOT NULL,
        MessageType TINYINT(1) DEFAULT 0,".  // 0 = Normal, 1 = Admin Notification (2 = Admin Help ticket?)
       "PRIMARY KEY(Id)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));

// Table for handling account recovery/resets. Also used for tracking security emails when dealing with email/password changes.
do_or_die(sql_query(
    "CREATE TABLE ".SECURITY_EMAIL_TABLE." (
        Email VARCHAR(64) NOT NULL,
        Timestamp INT(11) NOT NULL,
        MaxTimestamp INT(11) NOT NULL,
        Code VARCHAR(256) NOT NULL,
        Type VARCHAR(64) NOT NULL,".  // For other application uses.
       "Data VARCHAR(256) NOT NULL,".  // For other application uses.
       "Redirect VARCHAR(256) NOT NULL,
        PRIMARY KEY(Email)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));

// Table for holding all log messages.
do_or_die(sql_query(
    "CREATE TABLE ".SITE_LOGGING_TABLE." (
        Id INT(11) UNSIGNED AUTO_INCREMENT,".  // Just a unique ID, even though Timestamp should make it unique.
       "UserId INT(11) NOT NULL,
        Timestamp INT(11) NOT NULL,
        Action TEXT(256) NOT NULL,
        Section CHAR(1) NOT NULL,".  // ''=Site, R=Forums, G=Gallery, F=Fics, O=Oekaki (Site lists all sections as well).
       "Verbosity INT(11) NOT NULL,".  // Important actions = 1, Minor actions = 2.
       "PRIMARY KEY(Id, UserId, Timestamp)
    ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));

// Table cleanup events.
sql_query("CREATE EVENT delete_security_email_entries ON SCHEDULE EVERY 0:15 HOUR_MINUTE DO DELETE FROM ".SECURITY_EMAIL_TABLE." WHERE CURRENT_TIMESTAMP > MaxTimestamp;");

// TODO: Initialize file directories.

// TODO: Remove this call after testing is complete.
include_once("load_sql_mock_data.php");

// Creates the tag table and a item-tag map table. Default tag type is 'G'.
function CreateItemTagTables($tag_table_name, $item_tag_table_name, $alias_table_name, $implication_table_name, $item_id) {
    // Table for tags.
    do_or_die(sql_query(
        "CREATE TABLE $tag_table_name (
            TagId INT(11) UNSIGNED AUTO_INCREMENT,
            Name VARCHAR(".MAX_TAG_NAME_LENGTH.") NOT NULL,
            Type CHAR(1) DEFAULT 'M',".
           "EditLocked TINYINT(1) DEFAULT FALSE,
            AddLocked TINYINT(1) DEFAULT FALSE,
            CreatorUserId INT(11) NOT NULL,
            ChangeTypeUserId INT(11) NOT NULL,
            ChangeTypeTimestamp INT(11) NOT NULL,
            Note VARCHAR(".MAX_TAG_NOTE_LENGTH.") NOT NULL,
            PRIMARY KEY(TagId, Name)
        ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
    // Table for item-tag mapping.
    do_or_die(sql_query(
        "CREATE TABLE $item_tag_table_name (
            $item_id INT(11) NOT NULL,
            TagId INT(11) NOT NULL,
            PRIMARY KEY($item_id, TagId)
        ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
    // Table for tag aliases.
    do_or_die(sql_query(
        "CREATE TABLE $alias_table_name (
            TagId INT(11) NOT NULL,
            AliasTagId INT(11) NOT NULL,
            CreatorUserId INT(11) NOT NULL,
            Timestamp INT(11) NOT NULL,
            PRIMARY KEY(TagId)
        ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
    // Table for tag implications.
    do_or_die(sql_query(
        "CREATE TABLE $implication_table_name (
            TagId INT(11) NOT NULL,
            ImpliedTagId INT(11) NOT NULL,
            CreatorUserId INT(11) NOT NULL,
            Timestamp INT(11) NOT NULL,
            PRIMARY KEY(TagId, ImpliedTagId)
        ) DEFAULT CHARSET=utf8 COLLATE utf8_bin;"));
}
?>