<?php
// Page for viewing a single post.
// URL: /gallery/post/show/{post-id}/
// URL: /gallery/posts/viewpost.php?post={post-id}

include_once("../../header.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

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
    if (strlen($iter) > 0) $vars['poolIterator'] = $iter;
}

if (isset($user)) {
    // Settings for action perms flags.
    if (CanUserEditPost($user)) {
        $vars['canEdit'] = true;
        if ($post['Status'] != 'F' && $post['Status'] != 'D') {
            $vars['canFlag'] = true;
        }
    }
    if (CanUserDeletePost($user)) {
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
}

PreparePostStatistics($post);

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
    sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE ParentPostId=".$post['PostId'].";", 0) or RenderErrorPage("Post not found.");
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
?>