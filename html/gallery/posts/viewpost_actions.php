<?php
// Helper functions for applying user actions to a post.

include_once(SITE_ROOT."gallery/includes/functions.php");
include_once(SITE_ROOT."gallery/includes/image.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/tagging/tag_functions.php");

function InvalidActionBanner() {
    PostSessionBanner("Invalid action", "red");
}

function InsufficientPermissionBanner() {
    PostSessionBanner("Insufficient permissions", "red");
}

function ErrorBanner() {
    PostSessionBanner("Error processing action", "red");
}

function HandleEditAction($post) {
    global $user;
    if (!CanUserEditGalleryPost($user)) {
        InsufficientPermissionBanner();
        return;
    }
    if (!CanPerformSitePost()) MaintenanceError();
    if (!isset($_POST) ||
        !isset($_POST['rating']) ||
        mb_strlen($_POST['rating']) != 1 ||
        !isset($_POST['parent']) ||
        !(mb_strlen($_POST['parent']) == 0 || is_numeric($_POST['parent'])) ||
        !isset($_POST['source']) ||
        !isset($_POST['tags']) ||
        !isset($_POST['description'])) {
        InvalidActionBanner();
        return;
    }
    $pid = $post['PostId'];
    $parent_post_id = GetValidParentPostId($_POST['parent'], $pid);  // Ensure desired parent exists.
    $tagstr = "rating:".$_POST['rating']." parent:$parent_post_id source:".mb_substr(str_replace(" ", "%20", $_POST['source']), 0, 256)." ".$_POST['tags'];
    $tagstr = mb_ereg_replace("\s+", " ", $tagstr);
    $tagstrarray = explode(" ", $tagstr);
    $tagstrarray = array_filter($tagstrarray, function($str) { return mb_strlen($str) > 0; });
    $tagstr = implode(" ", $tagstrarray);
    UpdatePost($tagstr, $pid, $user);
    if (trim($_POST['description']) != trim($post['Description'])) {
        UpdatePostDescription($pid, $_POST['description'], $user);
    }
    PostSessionBanner("Post updated", "green");
}
function HandleApproveAction($post) {
    global $user;
    if (!CanUserApprovePost($user)) {
        InsufficientPermissionBanner();
        return;
    }
    if ($post['Status'] != 'P') {
        InvalidActionBanner();
        return;
    }
    $pid = $post['PostId'];
    if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET Status='A' WHERE PostId=$pid;")) {
        ErrorBanner();
        return;
    }
    $username = $user['DisplayName'];
    LogAction("<strong><a href='/user/$uid/'>$username</a></strong> approved <strong><a href='/gallery/post/show/$pid/'>post #$pid</a></strong>", "G");
    PostSessionBanner("Post approved", "green");
}
function HandleFlagAction($post) {
    global $user;
    if (!CanUserFlagGalleryPost($user)) {
        InsufficientPermissionBanner();
        return;
    }
    // Possible status are:
    // - A: Approved
    // - P: Pending
    // - F: Flagged
    // - D: Deleted.
    // - Maybe more?
    if ($post['Status'] == 'A' || $post['Status'] == 'P') {
        // Allow action if approved or pending.
    } else {
        InvalidActionBanner();
        return;
    }

    // Get reason text.
    if (!isset($_POST['reason-select'])) {
        InvalidActionBanner();
        return;
    }
    $reason = $_POST['reason-select'];
    $parent_post = -1;
    if (contains($reason, "#")) {
        // Require post number.
        if (!isset($_POST['extra-reason-text'])) {
            InvalidActionBanner();
            return;
        }
        $post_number = $_POST['extra-reason-text'];
        if (!is_numeric($post_number)) {
            InvalidActionBanner();
            return;
        }
        $post_number = (int)$post_number;
        $reason = $reason.$post_number;
        $parent_post = $post_number;
    }
    $uid = $user['UserId'];
    $pid = $post['PostId'];
    $reason = SanitizeHTMLTags($reason, NO_HTML_TAGS);  // Strip all tags.
    $reason = mb_substr($reason, 0, MAX_GALLERY_POST_FLAG_REASON_LENGTH);  // Trim to max length.
    $escaped_reason = sql_escape(GetSanitizedTextTruncated($reason, NO_HTML_TAGS, MAX_GALLERY_POST_FLAG_REASON_LENGTH));
    if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET Status='F', FlagReason='$escaped_reason', FlaggerUserId='$uid', ParentPostId=$parent_post WHERE PostId=$pid;")) {
        ErrorBanner();
        return;
    }
    $username = $user['DisplayName'];
    LogAction("<strong><a href='/user/$uid/'>$username</a></strong> flagged <strong><a href='/gallery/post/show/$pid/'>post #$pid</a></strong> <small><strong>Reason: </strong>$reason</small>", "G");
    PostSessionBanner("Post flagged", "green");
}
function HandleUnflagAction($post) {
    global $user;
    if (!CanUserUnflagGalleryPost($user)) {
        InsufficientPermissionBanner();
        return;
    }
    if ($post['Status'] != 'F') {
        InvalidActionBanner();
        return;
    }
    $pid = $post['PostId'];
    if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET Status='A' WHERE PostId=$pid;")) {
        ErrorBanner();
        return;
    }
    $username = $user['DisplayName'];
    LogAction("<strong><a href='/user/$uid/'>$username</a></strong> unflagged <strong><a href='/gallery/post/show/$pid/'>post #$pid</a></strong>", "G");
    PostSessionBanner("Post unflagged", "green");
}
function HandleDeleteAction($post) {
    global $user;
    if (!CanUserDeleteGalleryPost($user)) {
        InsufficientPermissionBanner();
        return;
    }
    if ($post['Status'] != 'F') {
        InvalidActionBanner();
        return;
    }
    $uid = $user['UserId'];  // Set flagger id as user who deleted (self).
    $pid = $post['PostId'];
    $ppid = $post['ParentPostId'];
    if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET Status='D', FlaggerUserId='$uid', ParentPostId=-1, ParentPoolId=-1, NumFavorites=0 WHERE PostId=$pid;")) {
        ErrorBanner();
        return;
    }
    $username = $user['DisplayName'];
    LogAction("<strong><a href='/user/$uid/'>$username</a></strong> deleted <strong><a href='/gallery/post/show/$pid/'>post #$pid</a></strong>", "G");
    // Transfer all user favorites to either parent post or named post.
    if ($ppid != -1) {
        // Try to transfer favorites. Don't worry about duplicates on UserId/PostId primary keys.
        if (sql_query_into($result, "SELECT * FROM ".GALLERY_USER_FAVORITES_TABLE." WHERE PostId=$pid;", 1)) {
            while ($row = $result->fetch_assoc()) {
                $fuid = $row['UserId'];
                sql_query("INSERT INTO ".GALLERY_USER_FAVORITES_TABLE." (UserId, PostId) VALUES ($fuid, $ppid)");
            }
        }
    }
    // Delete any remaining favorites.
    sql_query("DELETE FROM ".GALLERY_USER_FAVORITES_TABLE." WHERE PostId=$pid;");

    // Remove this as a parent from any other posts and transfer to parent.
    sql_query("UPDATE ".GALLERY_POST_TABLE." SET ParentPostId=$ppid WHERE ParentPostId=$pid;");

    UpdatePostStatistics($pid);
    UpdatePostStatistics($ppid);
    // Update all tag counts for tags on this post.
    UpdateAllTagCounts(GALLERY_TAG_TABLE, GALLERY_POST_TAG_TABLE, GALLERY_POST_TABLE, "PostId", "I.Status<>'D'", "EXISTS(SELECT 1 FROM ".GALLERY_POST_TAG_TABLE." PT WHERE PT.TagId=T.TagId AND PT.PostId=$pid)");
    PostSessionBanner("Post deleted", "green");
}
function HandleUndeleteAction($post) {
    global $user;
    if (!CanUserUndeleteGalleryPost($user)) {
        InsufficientPermissionBanner();
        return;
    }
    if ($post['Status'] != 'D') {
        InvalidActionBanner();
        return;
    }
    $uid = $user['UserId'];
    $pid = $post['PostId'];
    if (!sql_query("UPDATE ".GALLERY_POST_TABLE." SET Status='A' WHERE PostId=$pid;")) {
        ErrorBanner();
        return;
    }
    // Update all tag counts for tags on this post.
    UpdateAllTagCounts(GALLERY_TAG_TABLE, GALLERY_POST_TAG_TABLE, GALLERY_POST_TABLE, "PostId", "I.Status<>'D'", "EXISTS(SELECT 1 FROM ".GALLERY_POST_TAG_TABLE." PT WHERE PT.TagId=T.TagId AND PT.PostId=$pid)");
    $username = $user['DisplayName'];
    LogAction("<strong><a href='/user/$uid/'>$username</a></strong> un-deleted <strong><a href='/gallery/post/show/$pid/'>post #$pid</a></strong>", "G");
    PostSessionBanner("Post undeleted", "green");
}
function HandleAddCommentAction($post) {
    global $user;
    if (!CanUserCommentOnPost($user)) {
        InsufficientPermissionBanner();
        return;
    }
    if (!isset($_POST['text'])) {
        InvalidActionBanner();
        return;
    }
    $text = SanitizeHTMLTags($_POST['text'], DEFAULT_ALLOWED_TAGS);
    if (mb_strlen($text) < MIN_COMMENT_STRING_SIZE) {
        PostSessionBanner("Comment length is too short", "red");
        return;
    }
    $escaped_text = sql_escape(GetSanitizedTextTruncated($text, DEFAULT_ALLOWED_TAGS, MAX_GALLERY_COMMENT_LENGTH));
    $uid = $user['UserId'];
    $pid = $post['PostId'];
    $now = time();
    if (!sql_query("INSERT INTO ".GALLERY_COMMENT_TABLE." (PostId, UserId, CommentDate, CommentText) VALUES ($pid, $uid, $now, '$escaped_text');")) {
        ErrorBanner();
        return;
    }
    UpdatePostStatistics($pid);
    PostSessionBanner("Comment posted", "green");
}
function HandleDeleteCommentAction($post) {
    global $user;
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        InvalidActionBanner();
        return;
    }
    $cid = (int)$_POST['id'];
    $escaped_cid = sql_escape($cid);  // Just in case.
    if (!sql_query_into($result, "SELECT * FROM ".GALLERY_COMMENT_TABLE." WHERE CommentId='$escaped_cid';", 1)) {
        ErrorBanner();
        return;
    }
    $comment_to_delete = $result->fetch_assoc();
    if (!CanUserDeleteGalleryComment($user, $comment_to_delete)) {
        InsufficientPermissionBanner();
        return;
    }
    if (!sql_query("DELETE FROM ".GALLERY_COMMENT_TABLE." WHERE CommentId='$escaped_cid';")) {
        ErrorBanner();
        return;
    }
    UpdatePostStatistics($comment_to_delete['PostId']);
    PostSessionBanner("Comment deleted", "green");
}
function HandleAddFavoriteAction($post) {
    global $user;
    if ($post['Status'] == "D") {
        PostSessionBanner("Cannot add deleted post to favorites.", "red");
        return;
    }
    // All users can do this to self.
    $uid = $user['UserId'];
    $pid = $post['PostId'];
    $now = time();
    if (!sql_query("INSERT INTO ".GALLERY_USER_FAVORITES_TABLE." (UserId, PostId, Timestamp) VALUES ($uid, $pid, $now);")) {
        ErrorBanner();
        return;
    }
    UpdatePostStatistics($pid);
    PostSessionBanner("Added to Favorites", "green");
}
function HandleRemoveFavoriteAction($post) {
    global $user;
    // All users can do this to self.
    $uid = $user['UserId'];
    $pid = $post['PostId'];
    if (!sql_query("DELETE FROM ".GALLERY_USER_FAVORITES_TABLE." WHERE UserId=$uid AND PostId=$pid;")) {
        ErrorBanner();
        return;
    }
    UpdatePostStatistics($pid);
    PostSessionBanner("Removed from Favorites", "green");
}
function HandleSetAvatarAction($post) {
    global $user;
    if ($post['Status'] == "D") {
        PostSessionBanner("Cannot set avatar to a deleted post.", "red");
        return;
    }
    // All users can do this to self.
    $uid = $user['UserId'];
    $pid = $post['PostId'];
    if (!sql_query("UPDATE ".USER_TABLE." SET AvatarPostId=$pid, AvatarFname='' WHERE UserId=$uid;")) {
        ErrorBanner();
        return;
    }
    $fname = $user['AvatarFname'];
    if (strlen($fname)) {
        $path = SITE_ROOT."images/uploads/avatars/$fname";
        unlink($path);
    }
    $user['AvatarPostId'] = $pid;
    $user['AvatarFname'] = "";
    PostSessionBanner("Image set as Avatar", "green");
}
function HandleRegenThumbnailsAction($post) {
    global $user;
    if (!isset($user) || !CanUserRegenerateThumbnail($user, $post)) {
        InsufficientPermissionBanner();
        return;
    }
    CreateThumbnailFile($post['Md5'], $post['Extension']);
    $pid = $post['PostId'];
    $uid = $user['UserId'];
    $username = $user['DisplayName'];
    LogAction("<strong><a href='/user/$uid/'>$username</a></strong> regenerated thumbnail for <strong><a href='/gallery/post/show/$pid/'>post #$pid</a></strong>", "G");
    PostSessionBanner("Thumbnail created", "green");
}
?>