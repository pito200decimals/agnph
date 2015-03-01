<?php
// General setup script that will be used to set up the website.
// Should include any SQL database commands that are needed.

include_once("../includes/config.php");
include_once("../includes/constants.php");
include_once("../includes/util/core.php");
include_once("../includes/util/sql.php");

// If doesn't exist, is a no-op.
sql_query("DROP TABLE ".USER_TABLE.";");
sql_query("DROP TABLE ".SITE_NAV_TABLE.";");
sql_query("DROP TABLE ".FORUMS_THREAD_TABLE.";");
sql_query("DROP TABLE ".FORUMS_POST_TABLE.";");
sql_query("DROP TABLE ".FORUMS_USER_PREF_TABLE.";");

do_or_die(sql_query(
    "CREATE TABLE ".USER_TABLE." (
        UserId INT(11) UNSIGNED AUTO_INCREMENT,
        UserName VARCHAR(24) NOT NULL,
        DisplayName VARCHAR(24) NOT NULL,
        DisplayNameChangeTime INT(11) NOT NULL,
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
        JoinTime INT(11) NOT NULL,
        LastVisitTime INT(11) NOT NULL,
        KnownIPs VARCHAR(256) NOT NULL,
        Skin VARCHAR(16) DEFAULT 'agnph',
        PRIMARY KEY(UserId)
    );"));

do_or_die(sql_query(
    "CREATE TABLE ".SITE_NAV_TABLE." (
        Label VARCHAR(24) NOT NULL,
        Link VARCHAR(64) NOT NULL,
        ItemOrder INT(11) NOT NULL,
        PRIMARY KEY(Label, Link)
    );"));

do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_THREAD_TABLE." (
        ThreadId INT(11) UNSIGNED AUTO_INCREMENT,
        Title VARCHAR(256) NOT NULL,
        CreateDate INT(11) NOT NULL,
        CreatorUserId INT(11) NOT NULL,
        Posts VARCHAR(4096) NOT NULL,
        Locked TINYINT(1) DEFAULT 0 NOT NULL,
        PRIMARY KEY(ThreadId)
    );"));
    
do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_POST_TABLE." (
        PostId INT(11) UNSIGNED AUTO_INCREMENT,
        UserId INT(11) NOT NULL,
        PostDate INT(11) NOT NULL,
        EditDate INT(11) NOT NULL,
        Content VARCHAR(131072),
        PRIMARY KEY(PostId)
    );"));

do_or_die(sql_query(
    "CREATE TABLE ".FORUMS_USER_PREF_TABLE." (
        UserId INT(11) NOT NULL,
        Signature VARCHAR(256) NOT NULL,
        ThreadsPerPage INT(11) DEFAULT ".DEFAULT_THREADS_PER_PAGE.",
        PostsPerPage INT(11) DEFAULT ".DEFAULT_POSTS_PER_PAGE.",
        PRIMARY KEY(UserId)
    );"));
    
include_once("load_sql_mock_data.php");

?>