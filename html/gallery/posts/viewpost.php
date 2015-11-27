<?php
// Page for viewing a single post.
// URL: /gallery/post/show/{post-id}/
// URL: /gallery/posts/viewpost.php?post={post-id}

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."user/includes/functions.php");  // For avatar perms.
include_once(SITE_ROOT."gallery/posts/viewpost_actions.php");

// Layout of file:
// 1. Get post.
// 2. Process POST
// 3. Initialize page (post, comments, statistics, etc.)

if (!isset($_GET['post']) || !is_numeric($_GET['post'])) {
    InvalidURL();
}

// Fetch post.
$pid = (int)$_GET['post'];
$post = GetPost($pid) or RenderErrorPage("Post not found");

// Process actions.
if (isset($_POST['action'])) {
    if (!CanPerformSitePost()) MaintenanceError();
    // Post actions cannot occur when not logged in.
    if (!isset($user)) {
        PostBanner("Must be logged in to perform action", "red");
    } else {
        $action = $_POST['action'];
        switch ($action) {
            // Post actions.
            case "edit":
                HandleEditAction($post);
                break;
            case "approve":
                HandleApproveAction($post);
                break;
            case "flag":
                HandleFlagAction($post);
                break;
            case "unflag":
                HandleUnflagAction($post);
                break;
            case "delete":
                HandleDeleteAction($post);
                break;
            case "undelete":
                HandleUndeleteAction($post);
                break;
            // Add/Remove pool is done via AJAX on separate URL.
            case "add-comment":
                HandleAddCommentAction($post);
                break;
            case "delete-comment":
                HandleDeleteCommentAction($post);
                break;
            // User personal actions.
            case "add-favorite":
                HandleAddFavoriteAction($post);
                break;
            case "remove-favorite":
                HandleRemoveFavoriteAction($post);
                break;
            case "set-avatar":
                HandleSetAvatarAction($post);
                break;
            case "regen-thumbnail":
                HandleRegenThumbnailsAction($post);
                break;
            default:
                break;
        }
        // For all POST actions, redirect to this same page.
        Redirect($_SERVER['REQUEST_URI']);
    }
}

// Get ready to show the post, process all metadata.

// Get image URLs.
$md5 = $post['Md5'];
$ext = $post['Extension'];
if ($post['HasPreview']) {
    $post['previewUrl'] = GetSitePreviewPath($md5, $ext);
} else {
    $post['previewUrl'] = GetSiteImagePath($md5, $ext);
}
$post['downloadUrl'] = GetSiteImagePath($md5, $ext);
// Concatenate reverse-image search urls.
$post['googleUrl'] = "http://google.com/searchbyimage?image_url=".SITE_DOMAIN.GetSiteImagePath($md5, $ext);
$post['saucenaoUrl'] = "http://saucenao.com/search.php?url=".SITE_DOMAIN.GetSiteImagePath($md5, $ext);
$post['iqdbUrl'] = "http://iqdb.org/?url=".SITE_DOMAIN.GetSiteImagePath($md5, $ext);
$post['harryluUrl'] = "http://iqdb.harry.lu/?url=".SITE_DOMAIN.GetSiteImagePath($md5, $ext);

// Process tags.
$tags = GetTags($post['PostId']);
$tagNameStr = ToTagNameString($tags);
$tagCategories = ToTagCategorized($tags);
$post['tagstring'] = $tagNameStr;
$post['tagCategories'] = $tagCategories;

// Get other properties like uploader, rating HTML, etc.
FetchPostProperties($post) or RenderErrorPage("Post not found");

// Get previous, next posts.
if (sql_query_into($result, "SELECT PostId FROM ".GALLERY_POST_TABLE." WHERE PostId < $pid AND Status<>'D' ORDER BY PostId DESC LIMIT 1;", 1)) {
    $vars['prevPostId'] = $result->fetch_assoc()['PostId'];
}
if (sql_query_into($result, "SELECT PostId FROM ".GALLERY_POST_TABLE." WHERE PostId > $pid AND Status<>'D' ORDER BY PostId ASC LIMIT 1;", 1)) {
    $vars['nextPostId'] = $result->fetch_assoc()['PostId'];
}

// Get comments.
$comments = GetComments($post);
ConstructCommentBlockIterator($comments, $vars['commentIterator'], true /* allow_offset */,
    function($index) use ($pid) {
        $offset = ($index - 1) * GALLERY_COMMENTS_PER_PAGE;
        $url = "/gallery/post/show/$pid/?offset=$offset";
        return $url;
    }, GALLERY_COMMENTS_PER_PAGE);
$post['comments'] = $comments;

// Process user permissions for general actions.
if (isset($user)) {
    AddUserPermissions($post, $user);
}

// Increment view count, and do SQL after page is rendered.
if ($post['Status'] != 'D') $post['NumViews']++;
$vars['post'] = &$post;
RenderPage("gallery/posts/viewpost.tpl");
if ($post['Status'] != 'D' && !IsMaintenanceMode()) {
    sql_query("UPDATE ".GALLERY_POST_TABLE." SET NumViews = NumViews + 1 WHERE PostId=$pid;");
}
return;

function GetPost(&$pid) {
    $escaped_pid = sql_escape($pid);
    if (!sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE PostId='$escaped_pid';", 1)) return null;
    $post = $result->fetch_assoc();
    $pid = $post['PostId'];  // Get safe value, not user-generated.
    return $post;
}

function ToTagCategorized($allTags) {
    global $GALLERY_TAG_TYPES;
    $tagCategories = array();
    foreach ($GALLERY_TAG_TYPES as $char => $name) {
        $category = array();
        $category['name'] = $name;
        $category['tags'] = array();
        foreach ($allTags as $tag) {
            if ($tag['Type'] == $char) {
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
    return $tagCategories;
}

function GetUsers($user_ids) {
    $ret = array();
    $tables = array(USER_TABLE);
    if (!LoadTableData($tables, "UserId", $user_ids, $ret)) return null;
    return $ret;
}

function GetComments($post) {
    $pid = $post['PostId'];
    $comments = array();
    if (!sql_query_into($result, "SELECT * FROM ".GALLERY_COMMENT_TABLE." WHERE PostId=$pid ORDER BY CommentDate ASC, CommentId ASC;", 0)) return null;
    $uids = array();
    while ($row = $result->fetch_assoc()) {
        $uids[] = $row['UserId'];
        $comments[] = $row;
    }
    $uids = array_unique($uids);
    $users = GetUsers($uids);
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
            $comment['id'] = $comment['CommentId'];
        }
    }
    return $comments;
}

function AddUserPermissions(&$post, $user) {
    global $vars;
    $pid = $post['PostId'];
    $uid = $user['UserId'];
    $vars['isFavorited'] = sql_query_into($result, "SELECT * FROM ".GALLERY_USER_FAVORITES_TABLE." WHERE UserId=$uid AND PostId=$pid;", 1);
    // Settings for action perms flags.
    if (CanUserEditGalleryPost($user)) {
        $post['canEdit'] = true;
        if ($post['Status'] != 'F' && $post['Status'] != 'D') {
            $post['canFlag'] = true;
            $post['hasAction'] = true;
        }
    }
    if (CanUserDeleteGalleryPost($user)) {
        if ($post['Status'] == 'F') {
            $post['canDelete'] = true;
            $post['canUnflag'] = true;
            $post['hasAction'] = true;
        } else if ($post['Status'] == 'D') {
            $post['canUnDelete'] = true;
            $post['hasAction'] = true;
        }
    }
    if (CanUserApprovePost($user)) {
        if ($post['Status'] == 'P') {
            $post['canApprove'] = true;
            $post['hasAction'] = true;
        }
    }
    if (CanUserRegenerateThumbnail($user, $post)) {
        $post['canGenerateThumbnail'] = true;
        $post['hasAction'] = true;
    }
    if (CanUserAddOrRemoveFromPools($user)) {
        if ($post['Status'] != "D") {
            $post['canModifyPool'] = true;
            $post['hasAction'] = true;
        }
    }
    if ($post['Status'] != "D") {
        $post['canFavorite'] = true;
        $post['hasAction'] = true;
    }

    // Non-action-bar permissions.
    if (CanUserCommentOnPost($user)) {
        $post['canComment'] = true;
    }
    $ext = $post['Extension'];
    // Don't allow swf of webm posts to be used as an avatar.
    if (CanUserEditBasicInfo($user, $user) && $user['AvatarPostId'] != $pid &&
        ($ext == "jpg" || $ext == "png" || $ext == "gif")) {
        if ($post['Status'] != "D") {
            $post['canSetAvatar'] = true;
            $post['hasAction'] = true;
        }
    }
    // Add perms for all comments.
    foreach ($post['comments'] as &$comment) {
        $comment['actions'] = array();
        if (CanUserDeleteGalleryComment($user, $comment)) {
            $comment['actions'][] = array(
                // "url" => "",
                "action" => "delete-comment",
                "label" => "Delete",
                "confirmMsg" => "Are you sure you want to delete this comment?"
                );
        }
    }
}

function FetchPostProperties(&$post) {
    $postdate = $post['DateUploaded'];
    $poster_id = $post['UploaderId'];
    if (!sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId=$poster_id;", 1)) return false;
    $poster = $result->fetch_assoc();
    $post['postedHtml'] = "<span title='".FormatDate($postdate, GALLERY_DATE_LONG_FORMAT)."'>".FormatDuration(time() - $postdate)." ago</span> by <a href='/user/".$poster['UserId']."/gallery/'>".$poster['DisplayName']."</a>";
    switch ($post['Rating']) {
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
    sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE ParentPostId=".$post['PostId']." AND Status!='D';", 0);
    if ($result->num_rows > 0) {
        $post['hasChildren'] = true;
    }
    // Show status banners for pending, flagged, etc.
    CreateStatusBanner($post);
    // Get pool iterator box.
    $iter = CreatePoolIterator($post);
    if (mb_strlen($iter) > 0) $post['poolIterator'] = $iter;
    return true;
}

function CreateStatusBanner($post) {
    global $vars;
    // Find and link posts, if in the flag reason.
    $extra_msg = "";
    if ($post['Status'] == 'F' || $post['Status'] == 'D') {
        $pattern = "(post #?(\d+))";
        $groups = array();
        // When adding link to reason, escape characters. Not needed normally since template won't allow html.
        $reason = htmlspecialchars($post['FlagReason']);
        if (mb_eregi(".*$pattern.*", $reason, $groups) !== FALSE) {
            if (is_numeric($groups[2])) {
                $pid = (int)($groups[2]);
                $replacement = "<a href='/gallery/post/show/$pid/'>post #$pid</a>";
                $post['flagReasonWithLink'] = mb_eregi_replace($pattern, $replacement, $reason);
            }
        }
        if (isset($post['flagReasonWithLink']) && mb_strlen($post['flagReasonWithLink'])) {
            $extra_msg = ". Reason: ".$post['flagReasonWithLink'];
        } else if (isset($post['FlagReason']) && mb_strlen($post['FlagReason'])) {
            $extra_msg = ". Reason: ".SanitizeHTMLTags($post['FlagReason'], "" /*no tags*/);
        }
    }
    // Create the banner message.
    switch ($post['Status']) {
        case 'P':
            PostBanner("This post is pending moderator approval", "blue", false);
            break;
        case 'A':
            break;
        case 'F':
            $fuid = $post['FlaggerUserId'];
            if ($fuid > 0 && sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId=$fuid;", 1)) {
                $flagger = $result->fetch_assoc();
                $post['flagger'] = $flagger;
                $msg = "This post has been flagged for deletion by <a href='/user/".$flagger['UserId']."/gallery/'>".$flagger['DisplayName']."</a>";
            } else {
                $msg = "This post has been flagged for deletion";
            }
            $msg .= $extra_msg;
            PostBanner($msg, "red", false, true);
            break;
        case 'D':
            PostBanner("This post has been deleted".$extra_msg, "red", false);
            break;
        default:
            break;
    }
}

function CreatePoolIterator($post) {
    $pool_id = $post['ParentPoolId'];
    if (!sql_query_into($result, "SELECT * FROM ".GALLERY_POOLS_TABLE." WHERE PoolId=$pool_id;", 1)) return "";
    $pool = $result->fetch_assoc();
    $index = $post['PoolItemOrder'];
    $pool_url = "/gallery/post/?search=pool%3A".str_replace(" ", "_", $pool['Name']);
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

?>