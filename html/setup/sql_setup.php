<?php
// General setup script that will be used to set up the website.
// Should include any SQL database commands that are needed to create the
// site's database.

include_once("../includes/config.php");
include_once("../includes/constants.php");
include_once("../includes/util/core.php");
include_once("../includes/util/sql.php");

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
sql_query("DROP TABLE ".GALLERY_TAG_HISTORY_TABLE.";");
sql_query("DROP TABLE ".GALLERY_TAG_ALIAS_TABLE.";");
sql_query("DROP TABLE ".GALLERY_USER_PREF_TABLE.";");
sql_query("DROP TABLE ".GALLERY_USER_FAVORITES_TABLE.";");
sql_query("DROP TABLE ".GALLERY_POOLS_TABLE.";");
sql_query("DROP TABLE ".GALLERY_POOL_MAP_TABLE.";");

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
        KnownIPs VARCHAR(512) NOT NULL,".  // Allocate 45 + 1 characters for each IP address. Store the past 10 addresses comma-separated.
       "PRIMARY KEY(UserId)
    );"));
// User biography is stored in text files at /user/bio/{UserId}.txt

// TODO: Do we want this in a table, or in the site template?
do_or_die(sql_query(
    "CREATE TABLE ".SITE_NAV_TABLE." (
        Label VARCHAR(24) NOT NULL,
        Link VARCHAR(64) NOT NULL,
        ItemOrder INT(11) NOT NULL,
        PRIMARY KEY(Label, Link)
    );"));

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
    );"));
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
    );"));
// User preferences specific to the forums section.
do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_USER_PREF_TABLE." (
        UserId INT(11) NOT NULL,
        Signature VARCHAR(256) NOT NULL,
        SeenPostsUpToId INT(11) DEFAULT 0 NOT NULL,
        ThreadsPerPage INT(11) DEFAULT ".DEFAULT_THREADS_PER_PAGE.",
        PostsPerPage INT(11) DEFAULT ".DEFAULT_POSTS_PER_PAGE.",
        PRIMARY KEY(UserId)
    );"));
// Table containing rows of tuples of (UserId, PostId).
do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_UNREAD_POST_TABLE." (
        UserId INT(11) NOT NULL,
        PostId INT(11) NOT NULL,
        PRIMARY KEY(UserId, PostId)
    );"));


////////////////////
// Gallery tables //
////////////////////

// General information about a single post.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_POST_TABLE." (
        PostId INT(11) UNSIGNED AUTO_INCREMENT,
        Md5 CHAR(32) NOT NULL,
        Extension CHAR(4) NOT NULL,
        UploaderId INT(11) NOT NULL,
        DateUploaded INT(11) NOT NULL,
        Source VARCHAR(256) NOT NULL,
        Rating CHAR(1) NOT NULL,".  // s/q/e
       "Description TEXT(512) NOT NULL,
        ParentPostId INT(11) NOT NULL,
        Score INT(11) NOT NULL,
        Approval CHAR(1) NOT NULL,".  // P for pending, A for approved, F for flagged, D for deleted.
       "PRIMARY KEY(PostId)
    );"));
// General information about a single tag
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_TAG_TABLE." (
        TagId INT(11) UNSIGNED AUTO_INCREMENT,
        Name TEXT(32) NOT NULL,
        Type CHAR(1) NOT NULL,
        PRIMARY KEY(TagId)
    );"));
// Mapping of tag ids associated with each post.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_POST_TAG_TABLE." (
        PostId INT(11) NOT NULL,
        TagId INT(11) NOT NULL,
        PRIMARY KEY(PostId, TagId)
    );"));
// History of tag edits for a given post.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_TAG_HISTORY_TABLE." (
        PostId INT(11) NOT NULL,
        Timestamp INT(11) NOT NULL,
        TagsAdded VARCHAR(512) NOT NULL,
        TagsRemoved VARCHAR(512) NOT NULL,
        PRIMARY KEY(PostId, Timestamp)
    );"));
// Tag aliasing.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_TAG_ALIAS_TABLE." (
        TagId INT(11) NOT NULL,
        NewTagId INT(11) NOT NULL,
        PRIMARY KEY(TagId, NewTagId)
    );"));
// General information about pools.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_POOLS_TABLE." (
        PoolId INT(11) NOT NULL,
        Description TEXT(512) NOT NULL,
        PRIMARY KEY(PoolId)
    );"));
// Mapping of pools to posts/ordering.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_POOL_MAP_TABLE." (
        PoolId INT(11) NOT NULL,
        PostId INT(11) NOT NULL,
        ItemOrder INT(11) NOT NULL,
        PRIMARY KEY(PoolId, PostId)
    );"));
// User preferences for gallery section.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_USER_PREF_TABLE." (
        UserId INT(11) NOT NULL,
        UploadLimit INT(11) NOT NULL,
        Permissions CHAR(1) NOT NULL,
        PRIMARY KEY(UserId)
    );"));
// User favorites for gallery section.
do_or_die(sql_query(
    "CREATE TABLE ".GALLERY_USER_FAVORITES_TABLE." (
        UserId INT(11) NOT NULL,
        PostId INT(11) NOT NULL,
        Timestamp INT(11) NOT NULL,
        PRIMARY KEY(UserId, PostId)
    );"));

// TODO: Logging tables.

// TODO: Initialize file directories.

// TODO: Remove this call after testing is complete.
include_once("load_sql_mock_data.php");
?>