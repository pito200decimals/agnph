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
sql_query("DROP TABLE ".SITE_NAV_TABLE.";");
sql_query("DROP TABLE ".FORUMS_LOBBY_TABLE.";");
sql_query("DROP TABLE ".FORUMS_POST_TABLE.";");
sql_query("DROP TABLE ".FORUMS_USER_PREF_TABLE.";");
sql_query("DROP TABLE ".FORUMS_UNREAD_POST_TABLE.";");
sql_query("DROP TABLE ".GALLERY_POST_TABLE.";");
sql_query("DROP TABLE ".GALLERY_TAG_TABLE.";");
sql_query("DROP TABLE ".GALLERY_POST_TAG_TABLE.";");
sql_query("DROP TABLE ".GALLERY_POST_TAG_HISTORY_TABLE.";");
sql_query("DROP TABLE ".GALLERY_TAG_ALIAS_TABLE.";");
sql_query("DROP TABLE ".GALLERY_USER_PREF_TABLE.";");
sql_query("DROP TABLE ".GALLERY_USER_FAVORITES_TABLE.";");
sql_query("DROP TABLE ".GALLERY_POOLS_TABLE.";");
sql_query("DROP TABLE ".FICS_STORY_TABLE.";");
sql_query("DROP TABLE ".FICS_CHAPTER_TABLE.";");
sql_query("DROP TABLE ".FICS_TAG_TABLE.";");
sql_query("DROP TABLE ".FICS_STORY_TAG_TABLE.";");
sql_query("DROP TABLE ".FICS_REVIEW_TABLE.";");
sql_query("DROP TABLE ".FICS_USER_PREF_TABLE.";");

// Main user data table. General information that is shared between sections.
do_or_die(sql_query(
    "CREATE TABLE ".USER_TABLE." (
        UserId INT(11) UNSIGNED AUTO_INCREMENT,".  // User's ID.
        // User/admin/signup-assigned values.
       "UserName VARCHAR(24) UNIQUE NOT NULL,
        DisplayName VARCHAR(24) NOT NULL,
        Email VARCHAR(64) NOT NULL,
        Password CHAR(32) NOT NULL,
        SecretQuestion VARCHAR(256) NOT NULL,
        SecretAnswer CHAR(32) NOT NULL,
        Timezone FLOAT NOT NULL,
        Usermode INT(11) DEFAULT 1 NOT NULL,
        Permissions VARCHAR(8) NOT NULL,
        BanReason VARCHAR(256) NOT NULL,
        Title VARCHAR(64) NOT NULL,
        DOB CHAR(10) NOT NULL,
        ShowDOB TINYINT(1) DEFAULT 0 NOT NULL,
        Avatar VARCHAR(256) NOT NULL,
        Skin VARCHAR(16) DEFAULT 'agnph' NOT NULL,".
        // Code-assigned values.
       "JoinTime INT(11) NOT NULL,
        LastVisitTime INT(11) NOT NULL,
        DisplayNameChangeTime INT(11) NOT NULL,
        RegisterIP VARCHAR(50) NOT NULL,
        KnownIPs VARCHAR(512) NOT NULL,".  // Allocate 45 + 1 characters for each IP address. Store the past 10 addresses comma-separated.
       "PRIMARY KEY(UserId)
    ) DEFAULT CHARSET=utf8;"));
// User biography is stored in text files at /user/bio/{UserId}.txt

// TODO: Do we want this in a table, or in the site template?
do_or_die(sql_query(
    "CREATE TABLE ".SITE_NAV_TABLE." (
        Label VARCHAR(24) NOT NULL,
        Link VARCHAR(64) NOT NULL,
        ItemOrder INT(11) NOT NULL,
        PRIMARY KEY(Label, Link)
    ) DEFAULT CHARSET=utf8;"));

///////////////////
// Forums tables //
///////////////////

// Table specifying the structure of the general forum lobbies.
do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_LOBBY_TABLE." (
        LobbyId INT(11) UNSIGNED AUTO_INCREMENT,
        ParentLobbyId INT(11) NOT NULL,
        Name VARCHAR(64) NOT NULL,
        Description VARCHAR(512) NOT NULL,
        AccessPermissions VARCHAR(8) NOT NULL,
        PRIMARY KEY(LobbyId)
    ) DEFAULT CHARSET=utf8;"));
// Post table. ParentThreadId and ParentLobbyId are mutually exclusive.
// A Forum Thread is just the post id of the first post in the thread. This 
// first post cannot be deleted unless the whole thread's posts are deleted (or bulk en-masse by admins).
do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_POST_TABLE." (
        PostId INT(11) UNSIGNED AUTO_INCREMENT,
        UserId INT(11) NOT NULL,
        PostDate INT(11) NOT NULL,
        EditDate INT(11) DEFAULT 0 NOT NULL,
        Title VARCHAR(256) NOT NULL,
        ParentThreadId INT(11) DEFAULT -1 NOT NULL,
        ParentLobbyId INT(11) DEFAULT -1 NOT NULL,
        Content TEXT(131072),
        Sticky TINYINT(1) DEFAULT 0 NOT NULL,
        PostIP VARCHAR(45) NOT NULL,
        PRIMARY KEY(PostId)
    ) DEFAULT CHARSET=utf8;"));
// User preferences specific to the forums section.
do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_USER_PREF_TABLE." (
        UserId INT(11) NOT NULL,
        Signature VARCHAR(256) NOT NULL,
        SeenPostsUpToId INT(11) DEFAULT 0 NOT NULL,
        ForumThreadsPerPage INT(11) DEFAULT ".DEFAULT_FORUM_THREADS_PER_PAGE.",
        ForumPostsPerPage INT(11) DEFAULT ".DEFAULT_FORUM_POSTS_PER_PAGE.",
        ForumsPermissions CHAR(1) NOT NULL,".  // A = Allowed to sticky posts.
       "PRIMARY KEY(UserId)
    ) DEFAULT CHARSET=utf8;"));
// Table containing rows of tuples of (UserId, PostId).
do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_UNREAD_POST_TABLE." (
        UserId INT(11) NOT NULL,
        PostId INT(11) NOT NULL,
        PRIMARY KEY(UserId, PostId)
    ) DEFAULT CHARSET=utf8;"));


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
       "FlaggerUserId INT(11) NOT NULL,
        FlagReason VARCHAR(".MAX_GALLERY_POST_FLAG_REASON_LENGTH.") NOT NULL,
        PRIMARY KEY(PostId)
    ) DEFAULT CHARSET=utf8;"));
// Tag Types: A=Artist, C=Character, D=Copyright, G=General, S=Species (D is copyright for ordering reasons).
CreateItemTagTables(GALLERY_TAG_TABLE, GALLERY_POST_TAG_TABLE, "PostId");
// History of tag edits for a given post.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_POST_TAG_HISTORY_TABLE." (
        Id INT(11) UNSIGNED AUTO_INCREMENT,
        PostId INT(11) NOT NULL,
        Timestamp INT(11) NOT NULL,
        UserId INT(11) NOT NULL,
        TagsAdded VARCHAR(512) DEFAULT '',
        TagsRemoved VARCHAR(512) DEFAULT '',
        PropertiesChanged VARCHAR(512) DEFAULT '',
        PRIMARY KEY(Id, PostId, Timestamp)
    ) DEFAULT CHARSET=utf8;"));
// Tag aliasing.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_TAG_ALIAS_TABLE." (
        TagId INT(11) NOT NULL,
        NewTagId INT(11) NOT NULL,
        PRIMARY KEY(TagId, NewTagId)
    ) DEFAULT CHARSET=utf8;"));
// General information about pools.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_POOLS_TABLE." (
        PoolId INT(11) AUTO_INCREMENT,
        CreatorUserId INT(11) NOT NULL,
        Name VARCHAR(".MAX_POOL_NAME_LENGTH.") NOT NULL,
        Description TEXT(512) NOT NULL,
        PRIMARY KEY(PoolId)
    ) DEFAULT CHARSET=utf8;"));
// User preferences for gallery section.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_USER_PREF_TABLE." (
        UserId INT(11) NOT NULL,
        UploadLimit INT(11) NOT NULL,
        ArtistTagId INT(11) NOT NULL,
        GalleryPermissions CHAR(1) DEFAULT 'N',".  // N - Normal user, C - Contributor, A - Admin
       "GalleryPostsPerPage INT(11) DEFAULT ".DEFAULT_GALLERY_POSTS_PER_PAGE.",
        PRIMARY KEY(UserId)
    ) DEFAULT CHARSET=utf8;"));
// User favorites for gallery section.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_USER_FAVORITES_TABLE." (
        UserId INT(11) NOT NULL,
        PostId INT(11) NOT NULL,
        Timestamp INT(11) NOT NULL,
        PRIMARY KEY(UserId, PostId)
    ) DEFAULT CHARSET=utf8;"));


/////////////////
// Fics tables //
/////////////////

do_or_die(sql_query(
    "CREATE TABLE ".FICS_STORY_TABLE." (
        StoryId INT(11) UNSIGNED AUTO_INCREMENT,
        AuthorUserId INT(11) NOT NULL,
        CoAuthors VARCHAR(24) NOT NULL,".  // Comma-separated ids. Limit 3 co-authors (+ 1 author).
       "DateCreated INT(11) NOT NULL,
        DateUpdated INT(11) NOT NULL,
        Title VARCHAR(256) NOT NULL,
        Summary TEXT(4096) NOT NULL,
        Rating CHAR(11) NOT NULL,".  // G - G, P - PG, T - PG-13, R - R, X - XXX
       "ApprovalStatus CHAR(1) DEFAULT 'A',".  // P - Pending, A - Approved, F - Flagged, D - Deleted
       "Completed TINYINT(1) DEFAULT FALSE,
        ParentSeriesId INT(11) DEFAULT -1,
        SeriesItemOrder INT(11) NOT NULL,
        StoryNotes TEXT(1024) NOT NULL,
        ChapterCount INT(11) NOT NULL,
        WordCount INT(11) NOT NULL,
        Views INT(11) NOT NULL,
        TotalStars INT(11) NOT NULL,
        TotalRatings INT(11) NOT NULL,
        NumReviews INT(11) NOT NULL,
        PRIMARY KEY(StoryId)
    ) DEFAULT CHARSET=utf8;"));
do_or_die(sql_query(
    "CREATE TABLE ".FICS_CHAPTER_TABLE." (
        ChapterId INT(11) UNSIGNED AUTO_INCREMENT,
        ParentStoryId INT(11) NOT NULL,
        AuthorUserId INT(11) NOT NULL,
        Title VARCHAR(256) NOT NULL,
        ApprovalStatus CHAR(1) DEFAULT 'A',".  // P - Pending, A - Approved, F - Flagged, D - Deleted
       "ChapterItemOrder INT(11) NOT NULL,
        ChapterNotes TEXT(1024) NOT NULL,
        ChapterEndNotes TEXT(1024) NOT NULL,
        WordCount INT(11) NOT NULL,
        Views INT(11) NOT NULL,
        TotalStars INT(11) NOT NULL,
        TotalRatings INT(11) NOT NULL,
        NumReviews INT(11) NOT NULL,
        PRIMARY KEY(ChapterId)
    ) DEFAULT CHARSET=utf8;"));  // NOTE: WordCount, TotalStars and TotalRatings not implemented yet.
do_or_die(sql_query(
    "CREATE TABLE ".FICS_REVIEW_TABLE." (
        ReviewId INT(11) UNSIGNED AUTO_INCREMENT,
        StoryId INT(11) NOT NULL,
        ChapterId INT(11) NOT NULL,
        ReviewerUserId INT(11) NOT NULL,
        ReviewDate INT(11) NOT NULL,
        ReviewText TEXT(4096) NOT NULL,
        AuthorResponseText TEXT(1024) NOT NULL,
        ReviewScore INT(11) NOT NULL,
        IsReview TINYINT(1) NOT NULL,
        IsComment TINYINT(1) NOT NULL,
        PRIMARY KEY(ReviewId)
    ) DEFAULT CHARSET=utf8;"));
// Tag Types: C - Category, S - Species, W - Warning, H - Character, R - Series, G - General
CreateItemTagTables(FICS_TAG_TABLE, FICS_STORY_TAG_TABLE, "StoryId");
do_or_die(sql_query(
   "CREATE TABLE ".FICS_USER_PREF_TABLE." (
        UserId INT(11) NOT NULL,
        AuthorTagId INT(11) NOT NULL,
        FicsPermissions CHAR(1) DEFAULT 'N',".  // N - Normal user, A - Admin
       "FicsStoriesPerPage INT(11) DEFAULT ".DEFAULT_FICS_STORIES_PER_PAGE.",
        PRIMARY KEY(UserId)
    ) DEFAULT CHARSET=utf8;"));


// TODO: Logging tables.

// TODO: Initialize file directories.

// TODO: Remove this call after testing is complete.
include_once("load_sql_mock_data.php");

// Creates the tag table and a item-tag map table. Default tag type is 'G'.
function CreateItemTagTables($tag_table_name, $item_tag_table_name, $item_id) {
    do_or_die(sql_query(
        "CREATE TABLE $tag_table_name (
            TagId INT(11) UNSIGNED AUTO_INCREMENT,
            Name VARCHAR(".MAX_TAG_NAME_LENGTH.") NOT NULL,
            Type CHAR(1) DEFAULT 'G',".
           "EditLocked TINYINT(1) NOT NULL,
            AddLocked TINYINT(1) NOT NULL,
            CreatorUserId INT(11) NOT NULL,
            ChangeTypeUserId INT(11) NOT NULL,
            ChangeTypeTimestamp INT(11) NOT NULL,
            Count INT(11) NOT NULL,
            PRIMARY KEY(TagId)
        ) DEFAULT CHARSET=utf8;"));
    do_or_die(sql_query(
        "CREATE TABLE $item_tag_table_name (
            $item_id INT(11) NOT NULL,
            TagId INT(11) NOT NULL,
            PRIMARY KEY($item_id, TagId)
        ) DEFAULT CHARSET=utf8;"));
}
?>