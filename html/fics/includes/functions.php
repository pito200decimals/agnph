<?php
// General utility functions for the fics section.

include_once(SITE_ROOT."fics/includes/file.php");
include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");

function CanUserEditStory($story, $user) {
    return $user['UserId'] == $story['AuthorUserId'] || $user['FicsPermissions'] == 'A';
}
function CanUserDeleteStory($story, $user) {
    return $user['UserId'] == $story['AuthorUserId'] || $user['FicsPermissions'] == 'A';
}

// General path functions.
function GetChapterPath($cid) { return SITE_ROOT."fics/data/chapters/$cid.txt"; }

// Returns all info about a story, including its 'author' (and 'coauthors')
// Returns null on error.
function GetStory($sid) {
    $escaped_sid = sql_escape($sid);
    if (!sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE StoryId='$escaped_sid';", 1)) return null;
    $story = $result->fetch_assoc();
    FillStoryInfo($story);
    return $story;
}

function FillStoryInfo(&$story) {
    $author_ids = array();
    $author_ids[] = $story['AuthorUserId'];
    $coauthor_ids = explode(",", $story['CoAuthors']);
    foreach ($coauthor_ids as $coauthor_id) {
        if (strlen($coauthor_id) > 0) $author_ids[] = $coauthor_id;
    }

    $authors = array();
    if (!LoadTableData(array(USER_TABLE), "UserId", $author_ids, $authors)) return null;
    $story['coauthors'] = array();
    foreach ($authors as $author) {
        if ($author['UserId'] == $story['AuthorUserId']) $story['author'] = $author;
        else $story['coauthors'][] = $author;
    }

    // Expand shorthand like rating.
    switch ($story['Rating']) {
      case "G":
        $story['rating'] = "G";
        break;
      case "P":
        $story['rating'] = "PG";
        break;
      case "T":
        $story['rating'] = "PG-13";
        break;
      case "R":
        $story['rating'] = "R";
        break;
      case "X":
        $story['rating'] = "XXX";
        break;
    }

    // TODO
    $story['tags'] = GetTagsForStory($story['StoryId']);

    $story['DateCreated'] = FormatDate($story['DateCreated'], FICS_DATE_FORMAT);
    $story['DateUpdated'] = FormatDate($story['DateUpdated'], FICS_DATE_FORMAT);

    // TODO
    $story['reviewCount'] = 0;

    $stars = "";
    if ($story['TotalRatings'] > 0) {
        $averageStars = round($story['TotalStars'] / $story['TotalRatings']);
        for ($i = 1; $i < $averageStars; $i += 2) {
            $stars .= "<img src='/images/star.gif' />";
        }
        if ($i == $averageStars) {
            // Also add a half-star.
            $stars .= "<img src='/images/starhalf.gif' />";
        }
    }
    $story['stars'] = $stars;
}

// Gets all chapter metadata for a story (everything except chapter content).
// Returns null on error.
function GetChaptersInfo($sid) {
    $escaped_sid = sql_escape($sid);
    if (!sql_query_into($result, "SELECT * FROM ".FICS_CHAPTER_TABLE." WHERE ParentStoryId='$escaped_sid' ORDER BY ChapterItemOrder ASC, ChapterId DESC;", 1)) return null;
    $chapters = array();
    while ($row = $result->fetch_assoc()) {
        $chapters[] = $row;
    }
    return $chapters;
}

// Gets the chapter content for a story. Fills in ['title'], ['notes'], ['text'] and ['endnotes']
// Returns null on error.
function GetChapterContent($cid) {
    // TODO
    return array();
}

// Gets tag ids for story, and tag info.
function GetTagsForStory($sid) {
    // TODO
    return array();
}

// Gets info about tags.
function GetTagsInfo($tag_id_array) {
    // TODO
    return array();
}

function UpdateStoryStats($sid) {
    // TODO
}

function ChapterWordCount($content) {
    $stripped = SanitizeHTMLTags($content, "");
    debug($stripped);
    return sizeof(explode(" ", $stripped));
}

?>