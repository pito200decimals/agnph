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
    (UserId, Signature)
    VALUES
    (1, 'Sig of User 1'),
    (2, 'Sig of User 2'),
    (3, 'Sig of User 3');"));

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
function rand_date2() {
    return 0;
}


// Populate some threads
do_or_die(sql_query(
    "INSERT INTO ".FORUMS_LOBBY_TABLE."
    (LobbyId, ParentLobbyId, Name, Description)
    VALUES
    (1, -1, 'General Lobby', 'General Lobby Description'),
    (2, -1, 'Creative Lobby', 'Creative Lobby Description'),
    (3, -1, 'Other Lobby', 'Other Lobby Description'),
    (4, 1, 'News', 'News Description'),
    (5, 1, 'Support', 'Support Description'),
    (6, 2, 'Writing', 'Writing Description'),
    (7, 2, 'Art', 'Art Description'),
    (8, 3, 'Links', 'Links Description');"));
do_or_die(sql_query(
    "INSERT INTO ".FORUMS_THREAD_TABLE."
    (ThreadId, ParentLobbyId, Title, CreateDate, CreatorUserId)
    VALUES
    (1, 4, 'Title of thread 1', ".rand_date().", 1);"));
do_or_die(sql_query(
    "INSERT INTO ".FORUMS_POST_TABLE."
    (PostId, UserId, PostDate, EditDate, ParentThreadId, Content)
    VALUES
    (1, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 1'),
    (2, 2, ".rand_date().", ".rand_date2().", 1, 'Content of post 2'),
    (3, 3, ".rand_date().", ".rand_date2().", 1, 'Content of post 3'),
    (4, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 4'),
    (5, 2, ".rand_date().", ".rand_date2().", 1, 'Content of post 5'),
    (6, 3, ".rand_date().", ".rand_date2().", 1, 'Content of post 6'),
    (7, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 7'),
    (8, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 8'),
    (9, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 9'),
    (10, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 10'),
    (11, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 11'),
    (12, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 12'),
    (13, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 13'),
    (14, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 14'),
    (15, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 15'),
    (16, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 16'),
    (17, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 17'),
    (18, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 18'),
    (19, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 19'),
    (20, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 20'),
    (21, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 21'),
    (22, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 22'),
    (23, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 23'),
    (24, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 24'),
    (25, 1, ".rand_date().", ".rand_date2().", 1, 'Content of post 25');"));

?>