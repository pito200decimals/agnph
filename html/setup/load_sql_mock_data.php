<?php
// General script to load mock data into the SQL tables for testing purposes.

include_once("../includes/config.php");
include_once("../includes/constants.php");
include_once("../includes/util/core.php");
include_once("../includes/util/sql.php");
include_once("../gallery/includes/image.php");
include_once("../includes/util/file.php");
include_once("../gallery/includes/functions.php");
include_once("../fics/includes/functions.php");

delete_files("../user/data/");
delete_files("../user/data/bio/");
delete_files("../gallery/data/");
delete_files("../uploads/");
delete_files("../fics/data/");
delete_files("../fics/data/chapters/");
delete_files("../images/uploads/avatars/");
$oldmask = umask(0);
mkdir("../user/data/", 0777, true);
mkdir("../user/data/bio/", 0777, true);
mkdir("../gallery/data/", 0777, true);
mkdir("../uploads/", 0777, true);
mkdir("../fics/data/", 0777, true);
mkdir("../fics/data/chapters/", 0777, true);
mkdir("../images/uploads/avatars/", 0777, true);
umask($oldmask);


$now = time();
// Populate User table.
do_or_die(sql_query(
    "INSERT INTO ".USER_TABLE."
    (UserID, UserName, DisplayName, Email, Password, Usermode, DOB, Permissions, Title, Species, JoinTime, LastVisitTime)
    VALUES
    (1, 'User1', 'User1', 'user1@example.com', '".md5("Password 1")."', 1, '2003-02-01', 'A', 'Most Awesome Cyndaquil', 'Cyndaquil', $now, $now),
    (2, 'User2', 'User2', 'user2@example.com', '".md5("Password 2")."', 1, '2006-05-04', '', 'Hungry Resident', 'Totodile', $now, $now),
    (3, 'User3', 'User3', 'user3@example.com', '".md5("Password 3")."', 1, '2009-08-07', '', 'Generic Title', 'Chikorita', $now, $now);"));
// Forums settings.
do_or_die(sql_query(
    "INSERT INTO ".FORUMS_USER_PREF_TABLE."
    (UserId, Signature)
    VALUES
    (1, 'Sig of User1'),
    (2, 'Sig of User2'),
    (3, 'Sig of User3');"));
do_or_die(sql_query(
   "INSERT INTO ".GALLERY_USER_PREF_TABLE."
    (UserId, GalleryPermissions)
    VALUES
    (1, 'A'),
    (2, 'C'),
    (3, 'N');"));
do_or_die(sql_query(
   "INSERT INTO ".FICS_USER_PREF_TABLE."
    (UserId, FicsPermissions)
    VALUES
    (1, 'A'),
    (2, 'N'),
    (3, 'N');"));

WriteBio(1, "Bio of User1!<br />TEST");
WriteBio(2, "Bio of User2!");
WriteBio(3, "Bio of User3!");


// Populate site nav table.
do_or_die(sql_query(
    "INSERT INTO ".SITE_NAV_TABLE."
    (Label, Link, ItemOrder)
    VALUES
    ('News', '/index.php', 0),
    ('Forums', '/forums/', 1),
    ('Gallery', '/gallery/post/', 2),
    ('Fics', '/fics/', 3),
    ('Oekaki', '/oekaki/', 4),
    ('About', '/about/', 5),
    ('Setup', '/setup/sql_setup.php', 6);"));

do_or_die(sql_query(
    "INSERT INTO ".SITE_TEXT_TABLE."
    (Name, Text)
    VALUES
    ('RegisterDisclaimer', 'By clicking \'Register\', you agree that you are 18 years of age or older. A verification email will be sent to the provided email address.');"));
    
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
    $file = fopen("../user/data/bio/$uid.txt", "w");
    fwrite($file, $bio);
    fclose($file);
}

// Populate some gallery entries.

set_time_limit(90);
ini_set('memory_limit', '-1');

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
CreateTag("lucario", "S");
CreateTag("human", "S");
CreateTag("animated", "G");
CreateTag("koba", "A");
CreateTag("flareon", "S");
CreateTag("flygon", "S");
CreateTag("solo", "G");
CreateTag("floatzel", "S");

//*
CreateGalleryPost("c3024ba611837d85397d9661aec12840", "jpg", array("harlem", "umbreon", "quilava", "male"), "e");
CreateGalleryPost("16f7fdb2e63740e6dbf524e137899433", "png", array("syntex", "quilava", "raichu", "male"), "s");
CreateGalleryPost("0f80621ad5be140be8e3077bea316b06", "jpg", array("eroborus", "quilava", "dewott", "male"), "q");
CreateGalleryPost("42dba250ce52c9bfcdb3f3f6d3a1ef85", "jpg", array("harlem", "quilava", "typhlosion", "male"), "e", 1);
CreateGalleryPost("ff52157718c27a5bde447bbcba28fd85", "png", array("ahseo", "quilava", "male"), "q", -1, "P");
CreateGalleryPost("8014cdf559ca76698f7c1a2fbcd154dc", "png", array("redraptor16", "charizard", "female"), "s", -1, "F");
CreateGalleryPost("3a4332624e0689785296c334cab2d5d8", "jpg", array("evalion", "charizard", "male"), "e", 1);
CreateGalleryPost("2a9b345bc6db7cdc5dbbe6e4e13bb347", "jpg", array("doneru"));
CreateGalleryPost("84bb92189453fd60059f214e1902117c", "gif", array("lucario","human","male","female","animated"));
CreateGalleryPost("7f83ceaea3a928a72a5ef44ca28905a8", "jpg", array("quilava","human","male","female"));
CreateGalleryPost("ab907b0d22fdcba201a4fba3d20aef5b", "jpg", array("umbreon","flareon","male","female"));
CreateGalleryPost("85bb9ecdbbdbbbde574a5a8ae6798329", "jpg", array("umbreon","flareon","male","female"), "e", 11);
CreateGalleryPost("ba395299b5abdaaabb99938ab249283c", "jpg", array("umbreon","flareon","male","female"), "e", 11);
CreateGalleryPost("a096c523ac2044bc6ed13f069f474bf9", "jpg", array("flygon","male", "solo"));
// */
CreateGalleryPost("b4778c99464f01b56d8c3611143aad6f", "jpg", array("typhlosion","floatzel","male", "female"));

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

do_or_die(sql_query("INSERT INTO ".GALLERY_POOLS_TABLE."
    (Name, CreatorUserId)
    VALUES
    ('Test Pool 1', 1)
    ;"));
function AddToPool($post_id, $pool_id, $order = 0) {
    do_or_die(sql_query("UPDATE ".GALLERY_POST_TABLE." SET ParentPoolId=$pool_id,PoolItemOrder=$order WHERE PostId=$post_id;"));
}
AddToPool(1, 1, 1);
AddToPool(2, 1, 3);
AddToPool(3, 1, 2);
AddToPool(4, 1, 4);


// Populate some fics entries.

function CreateStory($author_id, $title, $summary, $story_notes, $rating = "X") {
    $now = time();
    do_or_die(sql_query("INSERT INTO ".FICS_STORY_TABLE."
        (AuthorUserId, DateCreated, DateUpdated, Title, Summary, StoryNotes, Rating, TotalStars, TotalRatings)
        VALUES
        ($author_id, $now, $now, '$title', '$summary', '$story_notes', '$rating', 13, 2);"));
}

CreateStory(1, "Title of story 1", "Test summary 1. This is a really long summary that probably cannot fit into the small mobile layout summary window, but will try nonetheless.", "Story notes");
CreateStory(1, "Quite a long title for a single story", "Test summary 2", "Story notes");
CreateStory(1, "Title of story 3", "Test summary 3", "Story notes");
CreateStory(1, "Title of story 4", "Test summary 4", "Story notes");
CreateStory(1, "Title of story 5", "Test summary 5", "Story notes");

function AddChapter($sid, $author_id, $title, $begin_notes, $content, $end_notes) {
    $result = false;
    sql_query_into($result, "SELECT count(*) FROM ".FICS_CHAPTER_TABLE." WHERE ParentStoryId=$sid;", 0);
    if (!$result) $count = 0;
    else $count = $result->fetch_assoc()['count(*)'];
    do_or_die(sql_query("INSERT INTO ".FICS_CHAPTER_TABLE."
        (ParentStoryId, AuthorUserId, Title, ChapterItemOrder, ChapterNotes, ChapterEndNotes, TotalStars, TotalRatings)
        VALUES
        ($sid, $author_id, '$title', $count, '$begin_notes', '$end_notes', 13, 2);"));
    // TODO: Write story content to file.
    $cid = sql_last_id();
    $chapter_path = GetChapterPath($cid);
    write_file($chapter_path, $content);
    // Update some story stats.
    $word_count = ChapterWordCount($content);
    $chapter_count = $count + 1;
    do_or_die(sql_query("UPDATE ".FICS_STORY_TABLE." SET ChapterCount=$chapter_count, WordCount=WordCount+$word_count WHERE StoryId=$sid;"));
}

AddChapter(1, 1, "Chapter 1 title", "BEGIN", "CONTENT 1", "END");
AddChapter(1, 1, "Chapter 2 title", "BEGIN", "CONTENT 2", "END");
AddChapter(1, 1, "Chapter 3 title", "BEGIN", "CONTENT 3", "END");

AddChapter(2, 1, "Chapter 1 title", "BEGIN", "CONTENT 1", "END");
AddChapter(3, 1, "Chapter 1 title", "BEGIN", "CONTENT 1", "END");
AddChapter(4, 1, "Chapter 1 title", "BEGIN", "CONTENT 1", "END");
AddChapter(5, 1, "Chapter 1 title", "BEGIN", "CONTENT 1", "END");

function AddReview($sid, $cid, $uid, $text, $score) {
    $escaped_text = sql_escape($text);
    $now = time();
    do_or_die(sql_query("INSERT INTO ".FICS_REVIEW_TABLE."
        (StoryId, ChapterId, ReviewerUserId, ReviewDate, ReviewText, ReviewScore, IsReview)
        VALUES
        ($sid, $cid, $uid, $now, '$escaped_text', $score, true);"));
    UpdateStoryStats($sid);
}

function AddComment($sid, $cid, $uid, $text, $score) {
    $escaped_text = sql_escape($text);
    $now = time();
    do_or_die(sql_query("INSERT INTO ".FICS_REVIEW_TABLE."
        (StoryId, ChapterId, ReviewerUserId, ReviewDate, ReviewText, ReviewScore, IsComment)
        VALUES
        ($sid, $cid, $uid, $now, '$escaped_text', $score, true);"));
    UpdateStoryStats($sid);
}

function AddScore($sid, $cid, $uid, $text, $score) {
    $escaped_text = sql_escape($text);
    $now = time();
    do_or_die(sql_query("INSERT INTO ".FICS_REVIEW_TABLE."
        (StoryId, ChapterId, ReviewerUserId, ReviewDate, ReviewText, ReviewScore)
        VALUES
        ($sid, $cid, $uid, $now, '$escaped_text', $score);"));
    UpdateStoryStats($sid);
}

AddReview(1, -1, 1, "Review text on main story with score 9", 9);
AddReview(1, -1, 1, "Review text on main story with score 5", 5);
AddReview(1, 1, 1, "Review text on chapter 1", 8);
AddReview(1, 2, 1, "Review text on chapter 2", 6);
AddReview(1, 3, 1, "Review text on chapter 3", 7);
AddComment(1, -1, 1, "Comment text on main story with score 9", 9);
AddComment(1, -1, 1, "Comment text on main story with score 5", 5);
AddComment(1, 1, 1, "Comment text on chapter 1", 8);
AddComment(1, 2, 1, "Comment text on chapter 2", 6);
AddComment(1, 3, 1, "Comment text on chapter 3", 7);

function AddTagsToStory($sid, $tagNames) {
    if (is_array($tagNames)) {
        $tags = GetTagsByName(FICS_TAG_TABLE, $tagNames, true, 1);
        $list = array_map(function($tag) use ($sid) {
            return "($sid, ".$tag['TagId'].")";
        }, $tags);
        $list = implode(",", $list);
        do_or_die(sql_query("INSERT INTO ".FICS_STORY_TAG_TABLE." (StoryId, TagId) VALUES $list;"));
    } else {
        AddTagsToStory($sid, array($tagNames));
    }
}

AddTagsToStory(1, array("Quilava", "Typhlosion", "Cyndaquil"));
AddTagsToStory(2, array("Typhlosion", "Cyndaquil"));
AddTagsToStory(3, array("Quilava", "Eevee", "Cyndaquil"));
AddTagsToStory(4, array("straight"));
AddTagsToStory(5, array("Quilava", "Typhlosion", "Cyndaquil"));

function CreatePM($sender_id, $recipient_id, $subject, $text, $parent = -1) {
    $now = time();
    do_or_die(sql_query("INSERT INTO ".USER_MAILBOX_TABLE."
        (SenderUserId, RecipientUserId, ParentMessageId, Timestamp, Title, Content)
        VALUES
        ($sender_id, $recipient_id, $parent, $now, '$subject', '$text');"));
    sleep(1);
}

CreatePM(1, 2, "TEST 1 to 2", "TEXT1");
CreatePM(3, 1, "TEST 3 to 1", "TEXT2");
CreatePM(2, 1, "RE: TEST 1 to 2", "TEXT3", 1);
CreatePM(2, 3, "TEST 2 to 3", "TEXT4");
CreatePM(2, 1, "RE: TEST 1 to 2", "TEXT5", 1);
CreatePM(1, 2, "RE: RE: TEST 1 to 2", "TEXT6", 5);
for ($i = 0; $i < 3; $i++) {
    CreatePM(2, 1, "TEST #$i", "TEXT".(7 + $i));
}

?>