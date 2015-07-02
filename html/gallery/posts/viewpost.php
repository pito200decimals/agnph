<?php
// Page for viewing a single post.
// URL: /gallery/post/show/{post-id}/
// URL: /gallery/posts/viewpost.php?post={post-id}

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");  // For avatar perms.

if (!isset($_GET['post'])) {
    InvalidURL();
}
// TODO: Show some sort of notification if this was resulting from a post edit.

$pid = $_GET['post'];
$escaped_post_id = sql_escape($pid);
sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_post_id';", 1) or RenderErrorPage("Post not found.");
$post = $result->fetch_assoc();
$pid = $post['PostId'];  // Get safe value, not user-generated.
$vars['post'] = &$post;

$md5 = $post['Md5'];
$ext = $post['Extension'];
if ($post['HasPreview']) {
    $vars['previewUrl'] = GetSitePreviewPath($md5, $ext);
} else {
    $vars['previewUrl'] = GetSiteImagePath($md5, $ext);
}
$vars['downloadUrl'] = GetSiteImagePath($md5, $ext);

$allTagIds = array();
sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TAG_TABLE." WHERE PostId=$pid;", 0) or RenderErrorPage("Post not found.");
while ($row = $result->fetch_assoc()) {
    $allTagIds[] = $row['TagId'];
}
$allTags = array();
if (sizeof($allTagIds) > 0) {
    sql_query_into($result, "SELECT * FROM ".GALLERY_TAG_TABLE." WHERE TagId IN (".implode(",", $allTagIds).");", 1) or RenderErrorPage("Post not found.");
    while ($row = $result->fetch_assoc()) {
        $allTags[] = $row;
    }
}

$tagNames = array_map(function($tag) { return $tag['Name']; }, $allTags);
sort($tagNames);
$post['tagstring'] = implode(" ", $tagNames);

$tagCategories = array();
foreach ($GALLERY_TAG_TYPES as $char => $name) {
    $category = array();
    $category['name'] = $name;
    $category['tags'] = array();
    foreach ($allTags as $tag) {
        if ($tag['Type'] == $char) {
            $tag['displayName'] = TagNameToDisplayName($tag['Name']);
            $category['tags'][] = $tag;
        }
    }
    if (sizeof($category['tags']) > 0) {
        usort($category['tags'], function($tag1, $tag2) {
            if ($tag1['Name'] == $tag2['Name']) return 0;
            return ($tag1['Name'] < $tag2['Name']) ? -1 : 1;
        });
        $tagCategories[] = $category;
    }
}
$vars['post']['tagCategories'] = $tagCategories;
if ($post['ParentPoolId'] != -1) {
    $iter = CreatePoolIterator($post);
    if (mb_strlen($iter) > 0) $vars['poolIterator'] = $iter;
}

if (isset($user)) {
    $uid = $user['UserId'];
    if (isset($_POST['favorite-action'])) {
        // Update favorite status.
        if ($_POST['favorite-action'] == "add") {
            $now = time();
            sql_query("INSERT INTO ".GALLERY_USER_FAVORITES_TABLE." (UserId, PostId, Timestamp) VALUES ($uid, $pid, $now);");
            UpdatePostStatistics($pid);
            $_SESSION['gallery_action_message'] = "Added to Favorites";
        } else if ($_POST['favorite-action'] == "remove") {
            sql_query("DELETE FROM ".GALLERY_USER_FAVORITES_TABLE." WHERE UserId=$uid AND PostId=$pid;");
            UpdatePostStatistics($pid);
            $_SESSION['gallery_action_message'] = "Removed from Favorites";
        }
    }
    if (isset($_POST['set-avatar-action'])) {
        // Set this post as our avatar.
        if (sql_query("UPDATE ".USER_TABLE." SET AvatarPostId=$pid, AvatarFname='' WHERE UserId=$uid;")) {
            $fname = $user['AvatarFname'];
            if (strlen($fname)) {
                $path = SITE_ROOT."images/uploads/avatars/$fname";
                unlink($path);
            }
            $user['AvatarPostId'] = $pid;
            $user['AvatarFname'] = "";
            $_SESSION['gallery_action_message'] = "Set as Avatar";
        }
    }
    // Check for user favorite.
    $vars['isFavorited'] = sql_query_into($result, "SELECT * FROM ".GALLERY_USER_FAVORITES_TABLE." WHERE UserId=$uid AND PostId=$pid;", 1);
    // Settings for action perms flags.
    if (CanUserEditGalleryPost($user)) {
        $vars['canEdit'] = true;
        if ($post['Status'] != 'F' && $post['Status'] != 'D') {
            $vars['canFlag'] = true;
        }
    }
    if (CanUserDeleteGalleryPost($user)) {
        if ($post['Status'] == 'F') {
            // TODO: Decide if admins can delete post from any state.
            $vars['canDelete'] = true;
            $vars['canUnflag'] = true;
        } else if ($post['Status'] == 'D') {
            $vars['canUnDelete'] = true;
        }
    }
    if (CanUserApprovePost($user)) {
        if ($post['Status'] == 'P') {
            $vars['canApprove'] = true;
        }
    }
    if (CanUserCommentOnPost($user)) {
        $vars['canComment'] = true;
    }
    if (CanUserEditBasicInfo($user, $user) && $user['AvatarPostId'] != $pid) {
        $vars['canSetAvatar'] = true;
    }
}
// Init comments.
// First, do a POST if we were previously posting a comment.
if (isset($_POST['text'])) {
    HandleCommentPOST($pid);
}
if (isset($_GET['offset']) && is_numeric($_GET['offset'])) {
    $comment_offset = (int)($_GET['offset']);
    if ($comment_offset < 0) $comment_offset = 0;
} else {
    $comment_offset = 0;
}
$comments = GetComments($pid);
ConstructCommentBlockIterator($comments, $vars['commentIterator'], true /* allow_offset */,
    function($index) use ($pid) {
        $offset = ($index - 1) * DEFAULT_GALLERY_COMMENTS_PER_PAGE;
        $url = "/gallery/post/show/$pid/?offset=$offset";
        return $url;
    }, DEFAULT_GALLERY_COMMENTS_PER_PAGE);
$vars['comments'] = $comments;

PreparePostStatistics($post);
PrepPostNotificationBanner($post);
HandleCreatingAllBanners($post);

// Increment view count, and do SQL after page is rendered.
$post['NumViews']++;
RenderPage("gallery/posts/viewpost.tpl");
sql_query("UPDATE ".GALLERY_POST_TABLE." SET NumViews = NumViews + 1 WHERE PostId=$pid;");
return;

function PreparePostStatistics(&$post) {
    $postdate = $post['DateUploaded'];
    $poster_id = $post['UploaderId'];
    sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId=$poster_id;", 1) or RenderErrorPage("Post not found.");
    $poster = $result->fetch_assoc();
    $post['postedHtml'] = FormatDuration(time() - $postdate)." ago by <a href='/user/".$poster['UserId']."/'>".$poster['DisplayName']."</a>";
    switch($post['Rating']) {
      case "s":
        $post['ratingHtml'] = "<span class='srating'>Safe</span>";
        break;
      case "q":
        $post['ratingHtml'] = "<span class='qrating'>Questionable</span>";
        break;
      case "e":
        $post['ratingHtml'] = "<span class='erating'>Explicit</span>";
        break;
    }
    sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE ParentPostId=".$post['PostId']." AND Status!='D';", 0) or RenderErrorPage("Post not found.");
    if ($result->num_rows > 0) {
        $post['hasChildren'] = true;
    }
}

function CreatePoolIterator($post) {
    $pool_id = $post['ParentPoolId'];
    if (!sql_query_into($result, "SELECT * FROM ".GALLERY_POOLS_TABLE." WHERE PoolId=$pool_id;", 1)) return "";
    $pool = $result->fetch_assoc();
    $index = $post['PoolItemOrder'];
    $pool_url = "/gallery/post/?search=pool%3A".$pool['PoolId'];
    if (!sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE ParentPoolId=$pool_id AND PoolItemOrder < $index ORDER BY PoolItemOrder DESC LIMIT 1;", 0)) return "";
    if ($result->num_rows > 0) {
        $prev_url = "/gallery/post/show/".$result->fetch_assoc()['PostId']."/";
    }
    if (!sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE ParentPoolId=$pool_id AND PoolItemOrder > $index ORDER BY PoolItemOrder ASC LIMIT 1;", 0)) return "";
    if ($result->num_rows > 0) {
        $next_url = "/gallery/post/show/".$result->fetch_assoc()['PostId']."/";
    }

    $prev_link = (isset($prev_url) ? "<a id='previnpool' href='$prev_url'>&lt;&lt;</a>" : "");
    $curr_link = "<a href='$pool_url'>".$pool['Name']."</a>";
    $next_link = (isset($next_url) ? "<a id='nextinpool' href='$next_url'>&gt;&gt;</a>" : "");
    return $prev_link . $curr_link . $next_link;
}
function GetComments($pid) {
    $escaped_pid = sql_escape($pid);
    $comments = array();
    if (!sql_query_into($result, "SELECT * FROM ".GALLERY_COMMENT_TABLE." WHERE PostId='$escaped_pid' ORDER BY CommentDate ASC, CommentId ASC;", 0)) return null;
    $ids = array();
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['UserId'];
        $comments[] = $row;
    }
    $ids = array_unique($ids);
    $users = GetUsers($ids);
    if ($users != null) {
        foreach ($users as &$usr) {
            $usr['avatarURL'] = GetAvatarURL($usr);
        }
        foreach ($comments as &$comment) {
            $uid = $comment['UserId'];
            // Set parameters that template expects.
            $comment['user'] = $users[$uid];
            $comment['date'] = FormatDate($comment['CommentDate'], GALLERY_DATE_FORMAT);
            $comment['title'] = "";
            $comment['text'] = $comment['CommentText'];
        }
    }
    return $comments;
}
function GetUsers($uids) {
    $ret = array();
    $tables = array(USER_TABLE);
    if (!LoadTableData($tables, "UserId", $uids, $ret)) return null;
    return $ret;
}
function HandleCommentPOST($pid) {
    global $user;
    if (!isset($user)) RenderErrorPage("You must be logged in to comment");
    if (!CanUserCommentOnPost($user)) RenderErrorPage("You are not authorized to comment");
    $text = SanitizeHTMLTags($_POST['text'], DEFAULT_ALLOWED_TAGS);
    if (mb_strlen($text) < MIN_COMMENT_STRING_SIZE) RenderErrorPage("Review length is too short");
    $escaped_text = sql_escape($text);
    $uid = $user['UserId'];
    $now = time();
    sql_query("INSERT INTO ".GALLERY_COMMENT_TABLE." (PostId, UserId, CommentDate, CommentText) VALUES ($pid, $uid, $now, '$escaped_text');");
    UpdatePostStatistics($pid);
    header("Location: /gallery/post/show/$pid/");
    exit();
}

// Links post and expands user for notification banners.
function PrepPostNotificationBanner(&$post) {
    // Get display name for flagger.
    if ($post['Status'] == 'F') {
        $uid = $post['FlaggerUserId'];
        if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId=$uid;", 1)) {
            $post['flagger'] = $result->fetch_assoc();
        }
    }
    // Find and link posts.
    if ($post['Status'] == 'F' || $post['Status'] == 'D') {
        $pattern = "(post (\d+))";
        $groups = array();
        // When adding link to reason, escape characters. Not needed normally since template won't allow html.
        $reason = htmlspecialchars ($post['FlagReason']);
        if (mb_eregi(".*$pattern.*", $reason, $groups) !== FALSE) {
            if (is_numeric($groups[2])) {
                $pid = (int)($groups[2]);
                $replacement = "<a href='/gallery/post/show/$pid/'>post #$pid</a>";
                $post['flagReasonWithLink'] = mb_eregi_replace($pattern, $replacement, $reason);
            }
        }
    }
}

function HandleCreatingAllBanners($post) {
    global $vars;
    $vars['banner_nofications'] = array();
    if ($post['Status'] == 'P') {
        $vars['banner_nofications'][] = array(
            "classes" => array("blue-banner"),
            "text" => "This post is pending moderator approval",
            "dismissable" => false,
            "strong" => true);
    } else if ($post['Status'] == 'F') {
        if (isset($post['flagger'])) {
            $msg = "This post has been flagged for deletion by <a href='/user/".$post['flagger']['UserId']."/gallery/'>".$post['flagger']['DisplayName']."</a>";
        } else {
            $msg = "This post has been flagged for deletion";
        }
        if (isset($post['flagReasonWithLink']) && mb_strlen($post['flagReasonWithLink'])) {
            $msg .= ". Reason: ".$post['flagReasonWithLink'];
        } else if (isset($post['FlagReason']) && mb_strlen($post['FlagReason'])) {
            $msg .= ". Reason: ".SanitizeHTMLTags($post['FlagReason'], "" /*no tags*/);
        }
        $vars['banner_nofications'][] = array(
            "classes" => array("red-banner"),
            "text" => $msg,
            "dismissable" => false,
            "strong" => true,
            "noescape" => true);
    } else if ($post['Status'] == 'D') {
        $vars['banner_nofications'][] = array(
            "classes" => array("red-banner"),
            "text" => "This post has been deleted",
            "dismissable" => false,
            "strong" => true);
    }
    if (isset($_SESSION['gallery_action_message'])) {
        $vars['banner_nofications'][] = array(
            "classes" => array("green-banner"),
            "text" => $_SESSION['gallery_action_message'],
            "dismissable" => true,
            "strong" => true);
        unset($_SESSION['gallery_action_message']);
    }
}
?>