<?php
// General utility functions for the fics section.

include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/comments/comments_functions.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."fics/includes/doc_reader.php");

function CanUserCreateStory($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'R') return false;
    return true;
}
function CanUserEditStory($story, $user) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'R') return false;
    if ($user['FicsPermissions'] == 'A') return true;
    if ($user['UserId'] == $story['AuthorUserId']) return true;
    if (in_array($user['UserId'], explode(",", $story['CoAuthors']))) return true;
    return false;
}
function CanUserChooseAnyAuthor($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'A') return true;
    return false;
}
function CanUserSetCoAuthors($story, $user) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'R') return false;
    if ($user['FicsPermissions'] == 'A') return true;
    if ($user['UserId'] == $story['AuthorUserId']) return true;
    if ($story['StoryId'] == -1) return true;  // Can set on creating story.
    return false;
}
function CanUserDeleteStory($story, $user) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'R') return false;
    if ($user['FicsPermissions'] == 'A') return true;
    if ($user['UserId'] == $story['AuthorUserId']) return true;
    return false;
}
function CanUserUndeleteStory($story, $user) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'A') return true;
    return false;
}
function CanUserSearchDeletedStories($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'A') return true;
    return false;
}
function CanUserComment($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'R') return false;
    return true;
}
function CanUserDeleteComment($user, $comment) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'R') return false;
    if ($user['FicsPermissions'] == 'A') return true;
    // TODO: Allow users and/or authors to delete comments on their story?
    // if ($user['UserId'] == $comment['ReviewerUserId']) return true;
    return false;
}
function CanUserReview($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'R') return false;
    return true;
}
function CanUserCreateFicsTags($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'R') return false;
    return true;
}
function CanUserFeatureStory($story, $user) {
    if (!IsUserActivated($user)) return false;
    if ($user['FicsPermissions'] == 'A') return true;
    return false;
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

    $story['tags'] = GetTagsById(FICS_TAG_TABLE, GetTagsIdsForStory($story['StoryId']));

    $story['DateCreated'] = FormatDate($story['DateCreated'], FICS_DATE_FORMAT);
    $story['DateUpdated'] = FormatDate($story['DateUpdated'], FICS_DATE_FORMAT);
    $story['stars'] = GetStars($story['TotalStars'], $story['TotalRatings']);

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
        $row['stars'] = GetStars($row['TotalStars'], $row['TotalRatings']);
        $row['ChapterNotes'] = SanitizeHTMLTags($row['ChapterNotes'], DEFAULT_ALLOWED_TAGS);
        $row['ChapterEndNotes'] = SanitizeHTMLTags($row['ChapterEndNotes'], DEFAULT_ALLOWED_TAGS);
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

// Gets tag ids for story.
function GetTagsIdsForStory($sid) {
    $escaped_sid = sql_escape($sid);
    if (!sql_query_into($result, "SELECT * FROM ".FICS_STORY_TAG_TABLE." WHERE StoryId=$escaped_sid;")) return array();
    $ret = array();
    while ($row = $result->fetch_assoc()) {
        $ret[] = $row['TagId'];
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
            $row['stars'] = GetStars($row['ReviewScore'], 1);
        }
        $row['id'] = $row['ReviewId'];
        // Initialize possible actions.
        $row['actions'] = array();
        if (isset($user) && CanUserDeleteComment($user, $row)) {
            $row['actions'][] = array(
                // "url" => "",
                "action" => "delete-comment",
                "label" => "Delete",
                "confirmMsg" => "Are you sure you want to delete this ".($row['IsReview'] ? "review" : "comment")."?"
                );
        }
        $reviews[] = $row;
    }
    $ids = array_unique($ids);
    $users = GetUsers($ids);
    if ($users != null) {
        foreach ($reviews as &$review) {
            $uid = $review['ReviewerUserId'];
            $usr = $users[$uid];
            $review['commenter'] = $usr;
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
// Also updates DateUpdated if flag is true.
function UpdateStoryStats($sid, $update_timestamp=false) {
    $escaped_sid = sql_escape($sid);
    $chapters = GetChaptersInfo($sid) or RenderErrorPage("Story not found");
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
    $update_sql = "ChapterCount=$chapcount, WordCount=$wordcount, Views=$viewcount";
    if (sql_query_into($result, "SELECT ".SCORES_THAT_COUNT." as C1, ".NUM_SCORES_THAT_COUNT." as C2, ".NUM_REVIEWS." as C3 FROM ".FICS_REVIEW_TABLE." R WHERE
        StoryId='$escaped_sid' OR
        (StoryId='$escaped_sid' AND
        EXISTS(SELECT 1 FROM ".FICS_CHAPTER_TABLE." C WHERE
            R.ChapterId=C.ChapterId AND
            C.ApprovalStatus='A'));", 0)) {
        $row = $result->fetch_assoc();
        $totalStars = $row['C1'];
        $totalRatings = $row['C2'];
        $numReviews = $row['C3'];
        $update_sql = $update_sql.", TotalStars=$totalStars, TotalRatings=$totalRatings, NumReviews=$numReviews";
    }
    if ($update_timestamp) {
        $now = time();
        $update_sql = $update_sql.", DateUpdated='$now'";
    }
    sql_query("UPDATE ".FICS_STORY_TABLE." SET $update_sql WHERE StoryId='$escaped_sid';");
}

function ChapterWordCount($content) {
    $stripped = SanitizeHTMLTags($content, "");
    $stripped = str_replace("\xC2\xA0", " ", $stripped);  // No multi-byte okay here. Replace &nbsp; with space.
    $words = explode(" ", $stripped);
    $words = array_filter($words, function($word) {
        return mb_strlen($word) > 0;
    });
    return sizeof($words);
}

function GetHashForChapter($sid, $cid) {
    return md5("$sid.$cid");
}

function GetStars($totalStars, $numReviews) {
    $stars = array();
    if ($numReviews > 0) {
        $averageStars = round($totalStars / $numReviews);
        for ($i = 1; $i < $averageStars; $i += 2) {
            $stars[] = "full";
        }
        if ($i == $averageStars) {
            // Also add a half-star.
            $stars[] = "half";
        }
    }
    return $stars;
}

// Functions to process word document uploads. Returns null on error.
function GetDocumentText($form_name) {
    if (!file_exists($_FILES[$form_name]['tmp_name']) || !is_uploaded_file($_FILES[$form_name]['tmp_name'])) return null;
    if ($_FILES[$form_name]['error']) return null;
    if ($_FILES[$form_name]['size'] > MAX_FILE_SIZE) return null;
    $file_parts = explode(".", $_FILES[$form_name]['name']);
    $extension = end($file_parts);
    if (!in_array($extension, array(
        "docx"
        ))) return null;
    if (!in_array($_FILES[$form_name]['type'], array(
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        ))) return null;
        
    // Read docx file and get contents.
    $doc_reader = new DocReader();
    $doc_reader->ReadDocument($_FILES[$form_name]['tmp_name']);
    $html = $doc_reader->ParseDocumentToHTML();
    return $html;
}

?>