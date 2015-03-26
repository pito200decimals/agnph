<?php
// General script to load mock data into the SQL tables for testing purposes.

include_once("../includes/config.php");
include_once("../includes/constants.php");
include_once("../includes/util/core.php");
include_once("../includes/util/sql.php");
include_once("../gallery/includes/image.php");
include_once("../includes/util/file.php");
include_once("../gallery/includes/functions.php");

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
delete_files("../user/bio/");
mkdir("../user/bio/");
WriteBio(1, "Bio of user 1!<br />TEST");
WriteBio(2, "Bio of user 2!");
WriteBio(3, "Bio of user 3!");


// Populate site nav table.
do_or_die(sql_query(
    "INSERT INTO ".SITE_NAV_TABLE."
    (Label, Link, ItemOrder)
    VALUES
    ('Home', '/index.php', 0),
    ('Forums', '/forums/', 1),
    ('Gallery', '/gallery/post/', 2),
    ('Fics', '/fics/', 3),
    ('Oekaki', '/oekaki/', 4),
    ('About', '/about/', 5),
    ('Setup', '/setup/sql_setup.php', 6);"));
    
function rand_date() {
    return mt_rand(0, 2147483647);
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
    "INSERT INTO ".FORUMS_POST_TABLE."
    (PostId, UserId, PostDate, ParentThreadId, ParentLobbyId, Title, Content)
    VALUES
    (1, 1, ".rand_date().", -1, 4, 'Title of thread 1', 'Content of post 1'),
    (2, 2, ".rand_date().", 1, -1, 'RE: Title of thread 1', 'Content of post 2'),
    (3, 3, ".rand_date().", 1, -1, 'RE: Title of thread 1', 'Content of post 3'),
    (4, 1, ".rand_date().", 1, -1, 'RE: Title of thread 1', 'Content of post 4'),
    (5, 2, ".rand_date().", 1, -1, 'RE: Title of thread 1', 'Content of post 5'),
    (6, 1, ".rand_date().", 1, -1, 'RE: Title of thread 1', 'Content of post 6'),
    (7, 2, ".rand_date().", 1, -1, 'RE: Title of thread 1', 'Content of post 7'),
    (8, 3, ".rand_date().", 1, -1, 'RE: Title of thread 1', 'Content of post 8'),
    (9, 1, ".rand_date().", 1, -1, 'RE: Title of thread 1', 'Content of post 9'),
    (10, 2, ".rand_date().", 1, -1, 'RE: Title of thread 1', 'Content of post 10'),
    (11, 3, ".rand_date().", -1, 4, 'Title of thread 2', 'Content of post 11'),
    (12, 1, ".rand_date().", -1, 5, 'Title of thread 3', 'Content of post 12');"));

function WriteBio($uid, $bio) {
    $file = fopen("../user/bio/$uid.txt", "w");
    fwrite($file, $bio);
    fclose($file);
}

// Populate some gallery entries.

set_time_limit(90);
ini_set('memory_limit', '-1');

delete_files("../gallery/data/");

function prep_file($md5, $ext) {
    $system_path = GetSystemImagePath($md5, $ext);
    $site_path = GetSiteImagePath($md5, $ext);
    $external_path = str_replace("/gallery/", "http://gallery.agn.ph/", $site_path);
    mkdirs(dirname($site_path));
    file_put_contents($system_path, fopen($external_path, 'r'));
    CreateThumbnailFile($md5, $ext);
    $preview_path = CreatePreviewFile($md5, $ext);
    if ($preview_path == GetSystemPreviewPath($md5, $ext)) {
        // Have a preview file.
        do_or_die(sql_query("UPDATE ".GALLERY_POST_TABLE." SET HasPreview=1 WHERE Md5='$md5';"));
    } else {
        // Used actual file, don't do any SQL.
    }
    debug("Processed $external_path");
}

function CreateTag($tag_name, $tag_type) {
    static $index = 1;
    do_or_die(sql_query(
        "INSERT INTO ".GALLERY_TAG_TABLE."
        (TagId, Name, Type)
        VALUES
        ($index, '$tag_name', '$tag_type');"));
    $index++;
}

function CreateGalleryPost($md5, $ext, $tag_names, $rating="e", $parentPostId="-1", $status="A") {
    static $index = 1;
    $tag_ids = array();
    $joined = implode(",", array_map(function($name) { return "'".sql_escape($name)."'"; }, $tag_names));
    do_or_die(sql_query_into($result, "SELECT TagId FROM ".GALLERY_TAG_TABLE." WHERE Name in ($joined);"));
    while ($row = $result->fetch_assoc()) {
        $tag_ids[] = $row['TagId'];
    }
    $now = time();
    do_or_die(sql_query(
        "INSERT INTO ".GALLERY_POST_TABLE."
        (PostId, Md5, Extension, UploaderId, Source, Rating, Description, Status, DateUploaded, ParentPostId)
        VALUES
        ($index, '$md5', '$ext', 1, 'PostSource $index', '$rating', 'PostDescription $index', '$status', $now, $parentPostId);"));
    foreach ($tag_ids as $tag_id) {
        do_or_die(sql_query(
            "INSERT INTO ".GALLERY_POST_TAG_TABLE."
            (PostId, TagId)
            VALUES
            ($index, $tag_id);"));
    }
    prep_file($md5, $ext);
    $index++;
}

CreateTag("harlem", "A");
CreateTag("umbreon", "S");
CreateTag("quilava", "S");
CreateTag("syntex", "A");
CreateTag("raichu", "S");
CreateTag("eroborus", "A");
CreateTag("dewott", "S");
CreateTag("typhlosion", "S");
CreateTag("ahseo", "A");
CreateTag("redraptor16", "A");
CreateTag("charizard", "S");
CreateTag("evalion", "A");
CreateTag("doneru", "A");
CreateTag("male", "G");
CreateTag("female", "G");

CreateGalleryPost("c3024ba611837d85397d9661aec12840", "jpg", array("harlem", "umbreon", "quilava", "male"), "e");
CreateGalleryPost("16f7fdb2e63740e6dbf524e137899433", "png", array("syntex", "quilava", "raichu", "male"), "s");
CreateGalleryPost("0f80621ad5be140be8e3077bea316b06", "jpg", array("eroborus", "quilava", "dewott", "male"), "q");
CreateGalleryPost("42dba250ce52c9bfcdb3f3f6d3a1ef85", "jpg", array("harlem", "quilava", "typhlosion", "male"), "e", 1);
CreateGalleryPost("ff52157718c27a5bde447bbcba28fd85", "png", array("ahseo", "quilava", "male"), "q", -1, "P");
CreateGalleryPost("8014cdf559ca76698f7c1a2fbcd154dc", "png", array("redraptor16", "charizard", "female"), "s", -1, "F");
CreateGalleryPost("3a4332624e0689785296c334cab2d5d8", "jpg", array("evalion", "charizard", "male"), "e", 1);
CreateGalleryPost("2a9b345bc6db7cdc5dbbe6e4e13bb347", "jpg", array("doneru"));

DoAllProcessTagString("doneru cyndaquil charizard typhlosion flygon male rating:s parent:2 source:test_url", 8, 1);

function CreateLotsOfFakeGallery($n) {
    for ($p = 0; $p < $n; $p+=100) {
        $sql = array();
        $choices = array("8014cdf559ca76698f7c1a2fbcd154dc", "ff52157718c27a5bde447bbcba28fd85", "16f7fdb2e63740e6dbf524e137899433");
        for ($i = $p; $i < $n && $i < $p + 100; $i++) {
            $sql[] = "('".$choices[mt_rand(0, sizeof($choices) - 1)]."', 'png', 1)";
        }
        do_or_die(sql_query(
            "INSERT INTO ".GALLERY_POST_TABLE."
            (Md5, Extension, UploaderId)
            VALUES
            ".implode(",", $sql).";"));
    }
}

// CreateLotsOfFakeGallery(120000);

?>