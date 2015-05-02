<?php
// General utility functions for the fics section.

include_once(SITE_ROOT."fics/includes/file.php");
include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/file.php");

function CanUserCreateStory($user) {
    return true;
}
function CanUserEditStory($story, $user) {
    return $user['UserId'] == $story['AuthorUserId'] || $user['FicsPermissions'] == 'A';
}
function CanUserDeleteStory($story, $user) {
    return $user['UserId'] == $story['AuthorUserId'] || $user['FicsPermissions'] == 'A';
}
function CanUserComment($user) {
    return true;
}
function CanUserReview($user) {
    return true;
}

// General path functions.
function GetChapterPath($cid) { return SITE_ROOT."fics/data/chapters/$cid.txt"; }

// Returns all info about a story, including its 'author' (and 'coauthors')
// Returns null on error.
function GetStory($sid) {
    if ($sid <= 0) return null;
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
        if (mb_strlen($coauthor_id) > 0) $author_ids[] = $coauthor_id;
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
    $story['stars'] = GetStarsHTML($story['TotalStars'], $story['TotalRatings']);
}

// Gets all chapter metadata for a story (everything except chapter content).
// Returns null on error.
function GetChaptersInfo($sid) {
    $escaped_sid = sql_escape($sid);
    if (!sql_query_into($result, "SELECT * FROM ".FICS_CHAPTER_TABLE." WHERE ParentStoryId='$escaped_sid' ORDER BY ChapterItemOrder ASC, ChapterId DESC;", 1)) return null;
    $chapters = array();
    while ($row = $result->fetch_assoc()) {
        $cid = $row['ChapterId'];
        $hash = GetHashForChapter($sid, $cid);
        $row['hash'] = $hash;
        $row['stars'] = GetStarsHTML($row['TotalStars'], $row['TotalRatings']);
        $chapters[] = $row;
    }
    return $chapters;
}

// Gets the chapter content for a story. Returns the chapter text.
// Returns null on error.
function GetChapterText($cid) {
    $retval = "";
    if (!read_file(GetChapterPath($cid), $retval)) return null;
    return $retval;
}

// Sets the chapter's text. Returns true on success.
function SetChapterText($cid, $text) {
    return write_file(GetChapterPath($cid), $text);
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

// Gets the array of reviews, or null if an error occurs.
function GetReviews($sid) {
    $escaped_sid = sql_escape($sid);
    $reviews = array();
    if (!sql_query_into($result, "SELECT * FROM ".FICS_REVIEW_TABLE." WHERE StoryId='$escaped_sid' ORDER BY ReviewDate ASC, ReviewId ASC;", 0)) return null;
    $ids = array();
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['ReviewerUserId'];
        $row['date'] = FormatDate($row['ReviewDate'], FICS_DATE_FORMAT);
        if ($row['IsReview'] && $row['ReviewScore'] > 0) {
            $row['stars'] = GetStarsHTML($row['ReviewScore'], 1);
        }
        $reviews[] = $row;
    }
    $ids = array_unique($ids);
    $users = GetUsers($ids);
    if ($users != null) {
        foreach ($reviews as &$review) {
            $uid = $review['ReviewerUserId'];
            $review['reviewer'] = $users[$uid];
        }
    }
    return $reviews;
}

function GetUsers($uids) {
    $ret = array();
    $tables = array(USER_TABLE);
    if (!LoadTableData($tables, "UserId", $uids, $ret)) return null;
    return $ret;
}

define("SCORES_THAT_COUNT", "SUM(CASE WHEN ReviewScore>0 THEN ReviewScore ELSE 0 END)");
define("NUM_SCORES_THAT_COUNT", "SUM(CASE WHEN ReviewScore>0 THEN 1 ELSE 0 END)");

// Does a full refresh on story stats. Updates ChapterCount, WordCount, TotalReviewStars, TotalReviews
function UpdateStoryStats($sid) {
    $escaped_sid = sql_escape($sid);
    $chapters = GetChaptersInfo($sid);
    $chapcount = 0;
    $wordcount = 0;
    $viewcount = 0;
    foreach ($chapters as $chapter) {
        $cid = $chapter['ChapterId'];
        $text = GetChapterText($cid);
        if ($text == null) continue;
        $chapcount++;
        $chapterWordCount = ChapterWordCount($text);
        $wordcount += $chapterWordCount;
        $viewcount += $chapter['Views'];
        // Also get chapter reviews.
        if (sql_query_into($result, "SELECT ".SCORES_THAT_COUNT.", ".NUM_SCORES_THAT_COUNT.", sum(IsReview) FROM ".FICS_REVIEW_TABLE." WHERE ChapterId=$cid;", 0)) {
            $row = $result->fetch_assoc();
            $totalStars = $row[SCORES_THAT_COUNT];
            $totalRatings = $row[NUM_SCORES_THAT_COUNT];
            $numReviews = $row['sum(IsReview)'];
            sql_query("UPDATE ".FICS_CHAPTER_TABLE." SET WordCount=$wordcount, TotalStars=$totalStars, TotalRatings=$totalRatings, NumReviews=$numReviews WHERE ChapterId=$cid;");
        } else {
            sql_query("UPDATE ".FICS_CHAPTER_TABLE." SET WordCount=$wordcount WHERE ChapterId=$cid;");
        }
    }

    // Also get story reviews.
    if (sql_query_into($result, "SELECT ".SCORES_THAT_COUNT.", ".NUM_SCORES_THAT_COUNT.", sum(IsReview) FROM ".FICS_REVIEW_TABLE." WHERE StoryId='$escaped_sid';", 0)) {
        $row = $result->fetch_assoc();
        $totalStars = $row[SCORES_THAT_COUNT];
        $totalRatings = $row[NUM_SCORES_THAT_COUNT];
        $numReviews = $row['sum(IsReview)'];
        sql_query("UPDATE ".FICS_STORY_TABLE." SET ChapterCount=$chapcount, WordCount=$wordcount, Views=$viewcount, TotalStars=$totalStars, TotalRatings=$totalRatings, NumReviews=$numReviews WHERE StoryId='$escaped_sid';");
    } else {
        sql_query("UPDATE ".FICS_STORY_TABLE." SET ChapterCount=$chapcount, WordCount=$wordcount, Views=$viewcount WHERE StoryId='$escaped_sid';");
    }
}

function ChapterWordCount($content) {
    $stripped = SanitizeHTMLTags($content, "");
    $words = explode(" ", $stripped);
    $words = array_filter($words, function($word) {
        return mb_strlen($word) > 0;
    });
    return sizeof($words);
}

function GetHashForChapter($sid, $cid) {
    return md5("$sid.$cid");
}

function GetStarsHTML($totalStars, $numReviews) {
    $stars = "";
    if ($numReviews > 0) {
        $averageStars = round($totalStars / $numReviews);
        for ($i = 1; $i < $averageStars; $i += 2) {
            $stars .= "<img src='/images/star.gif' />";
        }
        if ($i == $averageStars) {
            // Also add a half-star.
            $stars .= "<img src='/images/starhalf.gif' />";
        }
    }
    return $stars;
}

function ConstructReviewBlockIterator(&$items, &$iterator, $is_offset, $url_fn) {
    if (sizeof($items) > DEFAULT_FICS_COMMENTS_PER_PAGE) {
        if ($is_offset && isset($_GET['offset'])) $offset = $_GET['offset'];
        else $offset = 0;
        $iterator = Paginate($items, $offset, DEFAULT_FICS_COMMENTS_PER_PAGE,
            function($index, $current_page, $max_page) use ($url_fn) {
                if ($index == 0) {
                    if ($current_page == 1) {
                        return "";  // No link.
                    } else {
                        $url = $url_fn($current_page - 1);
                        return "<a href='$url'>&lt;&lt;</a>";
                    }
                } else if ($index == $max_page + 1) {
                    if ($current_page == $max_page) {
                        return "";  // No link.
                    } else {
                        $url = $url_fn($current_page + 1);
                        return "<a href='$url'>&gt;&gt;</a>";
                    }
                } else if ($index == $current_page) {
                    return "<a>[$index]</a>";  // No link.
                } else {
                        $url = $url_fn($index);
                    return "<a href='$url'>$index</a>";
                }
            }, true);
    }
}
?>