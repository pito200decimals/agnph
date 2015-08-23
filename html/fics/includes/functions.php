<?php
// General utility functions for the fics section.

include_once(SITE_ROOT."fics/includes/file.php");
include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/comments/comments_functions.php");

function CanUserCreateStory($user) {
    // Only registered users.
    if (!IsUserActivated($user)) return false;
    return true;
}
function CanUserEditStory($story, $user) {
    // Only author and admins.
    if (!IsUserActivated($user)) return false;
    return $user['UserId'] == $story['AuthorUserId'] || $user['FicsPermissions'] == 'A';
}
function CanUserDeleteStory($story, $user) {
    if (!IsUserActivated($user)) return false;
    return $user['UserId'] == $story['AuthorUserId'] || $user['FicsPermissions'] == 'A';
}
function CanUserUndeleteStory($story, $user) {
    if (!IsUserActivated($user)) return false;
    return $user['FicsPermissions'] == 'A';
}
function CanUserSearchDeletedStories($user) {
    if (!IsUserActivated($user)) return false;
    return $user['FicsPermissions'] == 'A';
}
function CanUserComment($user) {
    if (!IsUserActivated($user)) return false;
    return true;
}
function CanUserDeleteComment($user, $comment) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'A') return true;
    // TODO: Allow users to delete their own comments?
    // if ($user['UserId'] == $comment['UserId']) return true;
    return false;
}
function CanUserReview($user) {
    if (!IsUserActivated($user)) return false;
    return true;
}
function CanUserCreateFicsTags($user) {
    if (!IsUserActivated($user)) return false;
    return true;
}
function CanUserFeatureStory($story, $user) {
    if (!IsUserActivated($user)) return false;
    return $user['FicsPermissions'] == 'A';
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

    $story['tags'] = GetTagsInfo(GetTagsIdsForStory($story['StoryId']));

    $story['DateCreated'] = FormatDate($story['DateCreated'], FICS_DATE_FORMAT);
    $story['DateUpdated'] = FormatDate($story['DateUpdated'], FICS_DATE_FORMAT);
    $story['stars'] = GetStarsHTML($story['TotalStars'], $story['TotalRatings']);

    global $user;
    // Set up permissions.
    if (isset($user)) {
        if (CanUserEditStory($story, $user)) {
            $story['canEdit'] = true;
        }
        if (CanUserFeatureStory($story, $user)) {
            $story['canFeature'] = true;
        }
        if (CanUserDeleteStory($story, $user)) {
            $story['canDelete'] = true;
        }
        if (CanUserUndeleteStory($story, $user)) {
            $story['canUnDelete'] = true;
        }
        if (sql_query_into($result, "SELECT * FROM ".FICS_USER_FAVORITES_TABLE." WHERE UserId=".$user['UserId']." AND StoryId=".$story['StoryId'].";", 1)) {
            // Found a favorite.
            $story['canUnfavorite'] = true;
        } else {
            // Did not find a favorite.
            $story['canFavorite'] = true;
        }
    }
}

// Gets all chapter metadata for a story (everything except chapter content).
// Returns null on error.
function GetChaptersInfo($sid) {
    $escaped_sid = sql_escape($sid);
    if (!sql_query_into($result, "SELECT * FROM ".FICS_CHAPTER_TABLE." WHERE
        ParentStoryId='$escaped_sid' AND ApprovalStatus='A'
        ORDER BY ChapterItemOrder ASC, ChapterId DESC;", 1)) return null;
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
function GetTagsIdsForStory($sid) {
    $escaped_sid = sql_escape($sid);
    if (!sql_query_into($result, "SELECT * FROM ".FICS_STORY_TAG_TABLE." WHERE StoryId=$escaped_sid;")) return array();
    $ret = array();
    while ($row = $result->fetch_assoc()) {
        $ret[] = $row['TagId'];
    }
    return $ret;
}

// Gets info about tags, indexed by tag id.
function GetTagsInfo($tag_id_array) {
    if (sizeof($tag_id_array) == 0) return array();
    $joined = implode(",", $tag_id_array);
    if (!sql_query_into($result, "SELECT * FROM ".FICS_TAG_TABLE." WHERE TagId IN ($joined) ORDER BY Name;", 1)) return array();
    $ret = array();
    while ($row = $result->fetch_assoc()) {
        $row['class'] = mb_strtolower($row['Type'])."typetag";
        $ret[$row['TagId']] = $row;
    }
    return $ret;
}

// Gets the array of reviews, or null if an error occurs.
function GetReviews($sid) {
    global $user;
    $escaped_sid = sql_escape($sid);
    $reviews = array();
    if (!sql_query_into($result, "SELECT * FROM ".FICS_REVIEW_TABLE." R
        WHERE StoryId='$escaped_sid' AND
        (EXISTS(SELECT 1 FROM ".FICS_CHAPTER_TABLE." C WHERE
            R.ChapterId=C.ChapterId AND
            C.ApprovalStatus='A') OR
         ChapterId=-1)
        ORDER BY ReviewDate ASC, ReviewId ASC;", 1)) return null;
    $ids = array();
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['ReviewerUserId'];
        $row['date'] = FormatDate($row['ReviewDate'], FICS_DATE_FORMAT);
        if ($row['IsReview'] && $row['ReviewScore'] > 0) {
            $row['stars'] = GetStarsHTML($row['ReviewScore'], 1);
        }
        $row['id'] = $row['ReviewId'];
        if (isset($user)) {
            $row['canDelete'] = CanUserDeleteComment($user, $row);
        } else {
            $row['canDelete'] = false;
        }
        $reviews[] = $row;
    }
    $ids = array_unique($ids);
    $users = GetUsers($ids);
    if ($users != null) {
        foreach ($reviews as &$review) {
            $uid = $review['ReviewerUserId'];
            $review['commenter'] = $users[$uid];
        }
    }
    return $reviews;
}

function GetUsers($uids) {
    $ret = array();
    $tables = array(USER_TABLE);
    if (!LoadTableData($tables, "UserId", $uids, $ret)) return null;
    // Also initialize profile URLs.
    foreach ($ret as &$usr) {
        $usr['avatarURL'] = GetAvatarURL($usr);
    }
    return $ret;
}

define("SCORES_THAT_COUNT", "COALESCE(SUM(ReviewScore), 0)");
define("NUM_SCORES_THAT_COUNT", "COUNT(CASE WHEN ReviewScore>0 THEN 1 ELSE NULL END)");
define("NUM_REVIEWS", "COUNT(CASE WHEN IsReview THEN 1 ELSE NULL END)");

// Does a full refresh on story stats. Updates ChapterCount, WordCount, TotalReviewStars, TotalReviews, and ChapterItemOrder
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
        if ($chapter['ApprovalStatus'] == 'A') {
            $orderIndex = $chapcount;
            $chapcount++;
            $chapterWordCount = ChapterWordCount($text);
            $wordcount += $chapterWordCount;
            $viewcount += $chapter['Views'];
            // Also get chapter reviews.
            if (sql_query_into($result, "SELECT ".SCORES_THAT_COUNT." as C1, ".NUM_SCORES_THAT_COUNT." as C2, ".NUM_REVIEWS." as C3 FROM ".FICS_REVIEW_TABLE." WHERE ChapterId=$cid;", 0)) {
                $row = $result->fetch_assoc();
                $totalStars = $row['C1'];
                $totalRatings = $row['C2'];
                $numReviews = $row['C3'];
                sql_query("UPDATE ".FICS_CHAPTER_TABLE." SET ChapterItemOrder=$orderIndex, WordCount=$chapterWordCount, TotalStars=$totalStars, TotalRatings=$totalRatings, NumReviews=$numReviews WHERE ChapterId=$cid;");
            } else {
                sql_query("UPDATE ".FICS_CHAPTER_TABLE." SET ChapterItemOrder=$orderIndex, WordCount=$chapterWordCount WHERE ChapterId=$cid;");
            }
        }
    }

    // Also get story reviews.
    if (sql_query_into($result, "SELECT ".SCORES_THAT_COUNT." as C1, ".NUM_SCORES_THAT_COUNT." as C2, ".NUM_REVIEWS." as C3 FROM ".FICS_REVIEW_TABLE." R WHERE
        StoryId='$escaped_sid' AND
        EXISTS(SELECT 1 FROM ".FICS_CHAPTER_TABLE." C WHERE
            R.ChapterId=C.ChapterId AND
            C.ApprovalStatus='A');", 0)) {
        $row = $result->fetch_assoc();
        $totalStars = $row['C1'];
        $totalRatings = $row['C2'];
        $numReviews = $row['C3'];
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

?>