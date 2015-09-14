<?php
// Include page for processing story changes.

include_once(SITE_ROOT."fics/includes/functions.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/tagging/tag_functions.php");

if (!isset($user)) {
    return;
}
if (!CanPerformSitePost()) MaintenanceError();
if (!isset($_POST['sid'])) return;
$sid = $_POST['sid'];
if (!isset($_POST['title'])) return;
$title = SanitizeHTMLTags($_POST['title'], DEFAULT_ALLOWED_TAGS);
if (!isset($_POST['author'])) return;
$author_uid = $_POST['author'];
if (!isset($_POST['coauthors'])) $coauthor_ids = array();
else $coauthor_ids = $_POST['coauthors'];
if (!isset($_POST['summary'])) return;
$summary = SanitizeHTMLTags($_POST['summary'], DEFAULT_ALLOWED_TAGS);
if (!isset($_POST['rating'])) return;
$rating = $_POST['rating'];
if (!isset($_POST['completed'])) return;
$completed = $_POST['completed'];
if (!isset($_POST['notes'])) return;
$storynotes = SanitizeHTMLTags($_POST['notes'], DEFAULT_ALLOWED_TAGS);
if (!isset($_POST['tags'])) return;
$tagstring = CleanTagString($_POST['tags']);

// Check for valid input.
if (!is_numeric($sid)) return;
if (mb_strlen($title) == 0) {
    $errmsg = "Invalid Story Title";
}
if (!is_numeric($author_uid)) return;
foreach ($coauthor_ids as $id) {
    if (!is_numeric($id)) return;
}
if ($rating == '1') {
    $rating = 'G';
} else if ($rating == '2') {
    $rating = 'P';
} else if ($rating == '3') {
    $rating = 'T';
} else if ($rating == '4') {
    $rating = 'R';
} else if ($rating == '5') {
    $rating = 'X';
} else {
    return;
}
if ($completed == '1') {
    $completed = false;
} else if ($completed == '2') {
    $completed = true;
} else {
    return;
}

// Check chapter 1 input, if applicable.
if ($sid <= 0) {
    if (!isset($_POST['chaptertitle'])) return;
    $chaptertitle = SanitizeHTMLTags($_POST['chaptertitle'], DEFAULT_ALLOWED_TAGS);
    if (!isset($_POST['chapternotes'])) return;
    $chapternotes = SanitizeHTMLTags($_POST['chapternotes'], DEFAULT_ALLOWED_TAGS);
    if (!isset($_POST['chaptertext'])) return;
    $chaptertext = SanitizeHTMLTags($_POST['chaptertext'], DEFAULT_ALLOWED_TAGS);
    if (!isset($_POST['chapterendnotes'])) return;
    $chapterendnotes = SanitizeHTMLTags($_POST['chapterendnotes'], DEFAULT_ALLOWED_TAGS);
    if (mb_strlen($chaptertitle) == 0) {
        $errmsg = "Invalid Chapter Title";
    }
}

if (isset($errmsg) && mb_strlen($errmsg) > 0) return;

if ($sid > 0) {
    // Edit existing story.
    $story = GetStory($sid);
    if ($story == null) return;
    if (!CanUserEditStory($story, $user)) RenderErrorPage("Not authorized to edit story");
    if (!CanUserChooseAnyAuthor($user)) $author_uid = $user['UserId'];
    $sid = $story['StoryId'];
    $sets = array();
    if ($title != $story['Title']) {
        $escaped_title = sql_escape($title);
        $sets[] = "Title='$escaped_title'";
    }
    if (!$author_uid != $story['StoryId']) {
        // See if user actually exists.
        if (LoadSingleTableEntry(array(USER_TABLE), "UserId", $author_uid, $author)) {
            $sets[] = "AuthorUserId=".$author['UserId'];
        }
    }
    // Validate coauthors.
    if (CanUserSetCoAuthors($story, $user) &&
        sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId<>".$user['UserId']." AND UserId IN (".implode(",", $coauthor_ids).") LIMIT ".FICS_MAX_NUM_COAUTHORS.";", 0)) {
        $coauthor_ids = array();
        while ($row = $result->fetch_assoc()) {
            $coauthor_ids[] = $row['UserId'];
        }
        $coauthors = implode(",", $coauthor_ids);
        if ($coauthors != $story['CoAuthors']) {
            $sets[] = "CoAuthors='$coauthors'";
        }
    }
    if ($summary != $story['Summary']) {
        $escaped_summary = sql_escape($summary);
        $sets[] = "Summary='$escaped_summary'";
    }
    if ($rating != $story['Rating']) {
        $sets[] = "Rating='$rating'";
    }
    if ($completed != $story['Completed']) {
        $sets[] = "Completed=$completed";
    }
    if (CanUserFeatureStory($story, $user) && isset($_POST['featured'])) {
        $feature = $_POST['featured'];
        // Strlen and strpos because we store CHAR(1).
        if (strlen($feature) == 1 && strpos(FICS_NOT_FEATURED."FGSZfgsz", $feature) !== FALSE) {
            // Valid char.
            $escaped_feature = sql_escape($feature);
            $sets[] = "Featured='$escaped_feature'";
        }
    }
    if ($storynotes != $story['StoryNotes']) {
        $escaped_story_notes = sql_escape($storynotes);
        $sets[] = "StoryNotes='$escaped_story_notes'";
    }
    if (sizeof($sets) > 0) {
        // Don't update DateUpdated. Don't want to bump stories.
        $sets = implode(",", $sets);
        $success = sql_query("UPDATE ".FICS_STORY_TABLE." SET $sets WHERE StoryId=$sid;");
        if ($success) {
            $uid = $user['UserId'];
            $username = $user['DisplayName'];
            $storyTitle = htmlspecialchars($title);
            LogAction("<strong><a href='/user/$uid/'>$username</a></strong> edited story <strong><a href='/fics/story/$sid/'>$storyTitle</a></strong>", "F");
        }
    } else {
        // Nothing to change.
        $success = true;
    }
    // Also process tag changes.
    ProcessTagChanges($tagstring, $sid);
    return;
} else {
    if (!CanUserCreateStory($user)) RenderErrorPage("Not authorized to create a story");
    // Validate author.
    if (CanUserChooseAnyAuthor($user) && LoadSingleTableEntry(array(USER_TABLE), "UserId", $author_uid, $author)) {
        $author_uid = $author['UserId'];
    } else {
        $author_uid = $user['UserId'];
    }
    // Validate coauthors. Don't check perms, since you can always set coauthors on your new story.
    if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId<>$author_uid AND UserId IN (".implode(",", $coauthor_ids).") LIMIT ".FICS_MAX_NUM_COAUTHORS.";", 0)) {
        $coauthor_ids = array();
        while ($row = $result->fetch_assoc()) {
            $coauthor_ids[] = $row['UserId'];
        }
        $escaped_coauthors = sql_escape(implode(",", $coauthor_ids));
    } else {
        $escaped_coauthors = "";
    }
    // Create new story.
    $escaped_title = sql_escape($title);
    $escaped_summary = sql_escape($summary);
    $escaped_story_notes = sql_escape($storynotes);
    $chapter_count = 1;
    $word_count = ChapterWordCount($chaptertext);
    // Check min word count.
    $min_word_count = GetSiteSetting(FICS_CHAPTER_MIN_WORD_COUNT_KEY, DEFAULT_FICS_CHAPTER_MIN_WORD_COUNT);
    if ($min_word_count > 0) {
        if ($word_count < $min_word_count) {
            $errmsg = "Chapter must be at least $min_word_count words long. Current length: $word_count words";
            return;
        }
    }
    $uid = $user['UserId'];
    $now = time();
    if ($completed) $completed = "true";
    else $completed = "false";
    $success = sql_query("INSERT INTO ".FICS_STORY_TABLE."
        (AuthorUserId, Coauthors, DateCreated, DateUpdated, Title, Summary, Rating, Completed, ChapterCount, WordCount)
        VALUES
        ($author_uid, '$escaped_coauthors', $now, $now, '$escaped_title', '$escaped_summary', '$rating', $completed, $chapter_count, $word_count);");
    if (!$success) return;
    $sid = sql_last_id();
    $escaped_chap_title = sql_escape($chaptertitle);
    $escaped_chap_notes = sql_escape($chapternotes);
    $escaped_chap_endnotes = sql_escape($chapterendnotes);
    $success = sql_query("INSERT INTO ".FICS_CHAPTER_TABLE."
        (ParentStoryId, AuthorUserId, Title, ChapterItemOrder, ChapterNotes, ChapterEndNotes)
        VALUES
        ($sid, $uid, '$escaped_chap_title', 0, '$escaped_chap_notes', '$escaped_chap_endnotes');");
    if (!$success) {
        // Delete story.
        sql_query("DELETE FROM ".FICS_STORY_TABLE." WHERE StoryId=$sid;");
        return;
    }
    $cid = sql_last_id();
    // Write chapter contents to file.
    $success = SetChapterText($cid, $chaptertext);
    if (!$success) {
        // Delete story.
        sql_query("DELETE FROM ".FICS_CHAPTER_TABLE." WHERE StoryId=$cid;");
        sql_query("DELETE FROM ".FICS_STORY_TABLE." WHERE StoryId=$sid;");
        return;
    }

    $username = $user['DisplayName'];
    $storyTitle = htmlspecialchars($title);
    LogAction("<strong><a href='/user/$uid/'>$username</a></strong> created story <strong><a href='/fics/story/$sid/'>$storyTitle</a></strong>", "F");
}

// Changes tags for the story, and updates tag types.
function ProcessTagChanges($tag_string, $story_id) {
    global $user, $FICS_TAG_TYPES;
    $tokens = GetTagStringTokens($tag_string);
    $descriptors = GetTagDescriptors($tokens, $story_id, "FicsTagDescriptorFilterFn");
    UpdateStoryTags($descriptors, $story_id, $user);
    UpdateTagTypes(FICS_TAG_TABLE, $FICS_TAG_TYPES, $descriptors, $user);  // Do after creating tags above when setting post tags.
}

// Updates tags attached to a story.
function UpdateStoryTags($descriptors, $story_id) {
    global $user;
    $tag_names = array_map(function($desc) { return $desc->tag; }, $descriptors);
    $tags = GetTagsByNameWithAliasAndImplied(FICS_TAG_TABLE, FICS_TAG_ALIAS_TABLE, FICS_TAG_IMPLICATION_TABLE, $tag_names, CanUserCreateFicsTags($user), $user['UserId']);
    $tag_ids = array_map(function($tag) { return $tag['TagId']; }, $tags);
    $tag_ids_joined = implode(",", $tag_ids);
    if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TAG_TABLE." WHERE StoryId=$story_id;", 0)) {
        $tags_to_add = $tag_ids;
        $tags_to_remove = array();
        while ($row = $result->fetch_assoc()) {
            if (($key = array_search($row['TagId'], $tags_to_add)) === FALSE) {
                // Tag to delete.
                $tags_to_remove[] = $row['TagId'];
            } else {
                unset($tags_to_add[$key]);
            }
        }
        $tags = $tags + GetTagsById(FICS_TAG_TABLE, $tags_to_remove);
        $error = false;
        $tags_changed = false;
        $tags_to_remove = array_filter($tags_to_remove, function($tag_id) use ($tags) { return !$tags[$tag_id]['AddLocked']; });
        $tags_to_add = array_filter($tags_to_add , function($tag_id) use ($tags) { return !$tags[$tag_id]['AddLocked']; });
        if (sizeof($tags_to_remove) > 0) {
            $del_tag_ids_joined = implode(",", $tags_to_remove);
            sql_query("DELETE FROM ".FICS_STORY_TAG_TABLE." WHERE StoryId=$story_id AND TagId IN ($del_tag_ids_joined);");
        }
        if (sizeof($tags_to_add) > 0) {
            $post_tag_tuples = implode(",", array_map(function($tag_id) use ($story_id) {
                return "($story_id,$tag_id)";
            }, $tags_to_add));
            sql_query("INSERT INTO ".FICS_STORY_TAG_TABLE." (StoryId, TagId) VALUES $post_tag_tuples;");
        }
    }
}

// Filter function for tag type labels.
function FicsTagDescriptorFilterFn($token, $label, $tag, $story_id) {
    $obj = new stdClass();
    switch ($label) {
        case "category":
        case "species":
        case "warning":
        case "character":
        case "series":
        case "general":
            $obj->label = $label;
            $obj->tag = mb_strtolower($tag);
            $obj->isTag = true;
            break;
        default:
            $obj->label = "";
            $obj->tag = mb_strtolower($token);
            $obj->isTag = true;
            break;
    }
    return $obj;
}
?>