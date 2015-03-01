<?php
// General script to load mock data into the SQL tables for testing purposes.

include_once("../includes/config.php");
include_once("../includes/constants.php");
include_once("../includes/util/core.php");
include_once("../includes/util/sql.php");

// Populate User table.
do_or_die(sql_query(
    "INSERT INTO ".USER_TABLE."
    (UserID, UserName, DisplayName, Email, Password)
    VALUES
    (1, 'User 1', 'User 1', 'Email 1', '".md5("Password 1")."'),
    (2, 'User 2', 'User 2', 'Email 2', '".md5("Password 2")."'),
    (3, 'User 3', 'User 3', 'Email 3', '".md5("Password 3")."');"));
// Forums settings.
do_or_die(sql_query(
    "INSERT INTO ".FORUMS_USER_PREF_TABLE."
    (UserId)
    VALUES
    (1),
    (2),
    (3);"));

// Populate site nav table.
do_or_die(sql_query(
    "INSERT INTO ".SITE_NAV_TABLE."
    (Label, Link, ItemOrder)
    VALUES
    ('Home', '/index.php', 0),
    ('Forums', '/forums/', 1),
    ('Gallery', '/gallery/', 2),
    ('Fics', '/fics/', 3),
    ('Oekaki', '/oekaki/', 4),
    ('About', '/about/', 5);"));
    
function rand_date() {
    return mt_rand(0, 2147483647)*2+mt_rand(0,1);
}


// Populate some threads
do_or_die(sql_query(
    "INSERT INTO ".FORUMS_THREAD_TABLE."
    (ThreadId, Title, CreateDate, CreatorUserId, Posts)
    VALUES
    (1, 'Title of thread 1', ".rand_date().", 1, '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15'),
    (2, 'Title of thread 2', ".rand_date().", 2, '3,5,6,7');"));
do_or_die(sql_query(
    "INSERT INTO ".FORUMS_POST_TABLE."
    (PostId, UserId, PostDate, EditDate, Content)
    VALUES
    (1, 1, ".rand_date().", ".rand_date().", 'Content of post 1'),
    (2, 2, ".rand_date().", ".rand_date().", 'Content of post 2'),
    (3, 3, ".rand_date().", ".rand_date().", 'Content of post 3'),
    (4, 1, ".rand_date().", ".rand_date().", 'Content of post 4'),
    (5, 2, ".rand_date().", ".rand_date().", 'Content of post 5'),
    (6, 3, ".rand_date().", ".rand_date().", 'Content of post 6'),
    (7, 1, ".rand_date().", ".rand_date().", 'Content of post 7'),
    (8, 1, ".rand_date().", ".rand_date().", 'Content of post 8'),
    (9, 1, ".rand_date().", ".rand_date().", 'Content of post 9'),
    (10, 1, ".rand_date().", ".rand_date().", 'Content of post 10'),
    (11, 1, ".rand_date().", ".rand_date().", 'Content of post 11'),
    (12, 1, ".rand_date().", ".rand_date().", 'Content of post 12'),
    (13, 1, ".rand_date().", ".rand_date().", 'Content of post 13'),
    (14, 1, ".rand_date().", ".rand_date().", 'Content of post 14'),
    (15, 1, ".rand_date().", ".rand_date().", 'Content of post 15');"));

?>