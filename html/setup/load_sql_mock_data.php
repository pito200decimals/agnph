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
    ('Gallery', '/gallery/', 2),
    ('Fics', '/fics/', 3),
    ('Oekaki', '/oekaki/', 4),
    ('About', '/about/', 5);"));
    
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
function delete_files($target) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
        
        foreach( $files as $file )
        {
            delete_files( $file );      
        }
      
        rmdir( $target );
    } elseif(is_file($target)) {
        unlink( $target );  
    }
}

// Populate some gallery entries.
do_or_die(sql_query(
    "INSERT INTO ".GALLERY_POST_TABLE."
    (PostId, Md5, Extension, UploaderId, Source, Description)
    VALUES
    (1, 'c3024ba611837d85397d9661aec12840', 'jpg', 1, 'PostSource 1', 'PostDescription 1'),
    (2, '16f7fdb2e63740e6dbf524e137899433', 'png', 1, 'PostSource 2', 'PostDescription 2'),
    (3, '0f80621ad5be140be8e3077bea316b06', 'jpg', 1, 'PostSource 3', 'PostDescription 3'),
    (4, '42dba250ce52c9bfcdb3f3f6d3a1ef85', 'jpg', 1, 'PostSource 4', 'PostDescription 4'),
    (5, 'ff52157718c27a5bde447bbcba28fd85', 'png', 1, 'PostSource 5', 'PostDescription 5'),
    (6, '8014cdf559ca76698f7c1a2fbcd154dc', 'png', 1, 'PostSource 6', 'PostDescription 6'),
    (7, '3a4332624e0689785296c334cab2d5d8', 'jpg', 1, 'PostSource 7', 'PostDescription 7');"));

include_once("../gallery/includes/image.php");

delete_files("../gallery/data/");
mkdir("../gallery/data/");
mkdir("../gallery/data/thumb/");
prep_file("c3024ba611837d85397d9661aec12840", "jpg");
prep_file("16f7fdb2e63740e6dbf524e137899433", "png");
prep_file("0f80621ad5be140be8e3077bea316b06", "jpg");
prep_file("42dba250ce52c9bfcdb3f3f6d3a1ef85", "jpg");
prep_file("ff52157718c27a5bde447bbcba28fd85", "png");
prep_file("8014cdf559ca76698f7c1a2fbcd154dc", "png");
prep_file("3a4332624e0689785296c334cab2d5d8", "jpg");


function prep_file($md5, $ext) {
    $path = "";
    $path .= substr($md5, 0, 2)."/";
    mkdir("../gallery/data/$path/");
    mkdir("../gallery/data/thumb/$path/");
    $path .= substr($md5, 2, 2)."/";
    mkdir("../gallery/data/$path/");
    mkdir("../gallery/data/thumb/$path/");
    $path .= "$md5.$ext";
    file_put_contents("../gallery/data/$path", fopen("http://gallery.agn.ph/data/$path", 'r'));
    $image = new SimpleImage();
    $image->load("../gallery/data/$path");
    if ($image->getWidth() > $image->getHeight()) {
        $image->resizeToWidth(150);
    } else {
        $image->resizeToHeight(150);
    }
    $image->save("../gallery/data/thumb/$path");
    debug("Processed http://gallery.agn.ph/data/$path");
}

// Populate some tags.
do_or_die(sql_query(
    "INSERT INTO ".GALLERY_TAG_TABLE."
    (TagId, Name, Type)
    VALUES
    (1, 'darkmirage', 'A'),
    (2, 'jem', 'C'),
    (3, 'male', 'G'),
    (4, 'solo', 'G'),
    (5, 'quilava', 'S'),
    (6, 'typhlosion', 'S');"));

do_or_die(sql_query(
    "INSERT INTO ".GALLERY_POST_TAG_TABLE."
    (PostId, TagId)
    VALUES
    (1, 2),
    (1, 3),
    (1, 4),
    (1, 5),
    (1, 6),
    (2, 1),
    (2, 3),
    (2, 4),
    (2, 5),
    (2, 6),
    (3, 1),
    (3, 2),
    (3, 4),
    (3, 5),
    (3, 6),
    (4, 1),
    (4, 2),
    (4, 3),
    (4, 5),
    (4, 6),
    (5, 1),
    (5, 2),
    (5, 3),
    (5, 4),
    (5, 6);"));

?>