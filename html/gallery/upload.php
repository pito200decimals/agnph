<?php
// Page for uploading a post. Handles both upload form and the upload itself
// (which redirects to the post page after success).

define("PRETTY_PAGE_NAME", "Gallery");

include_once("../header.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($user)) {
    RenderErrorPage("You must be logged in to upload a post");
    return;
}
if (!CanUserUploadPost($user)) {
    if (GalleryEditRegistrationPeriodNotOver($user)) {
        RenderErrorPage("You are not authorized to upload new posts. You must wait ".GALLERY_EDITS_AFTER_REGISTRATION_DEADLINE_STRING." after registering before you can upload new posts");
    } else {
        RenderErrorPage("You are not authorized to upload new posts");
    }
    return;
}
if (!QuickCanUserUpload($user)) {
    RenderErrorPage("You have reached your upload limit. Please wait for your pending uploads to be approved");
    return;
}
if (!CanPerformSitePost()) MaintenanceError();

if ((!(!isset($_FILES['file']['error']) || is_array($_FILES['file']['error']) || empty($_FILES['file']['name'])) || isset($_POST['source'])) &&
    isset($_POST['tags']) &&
    isset($_POST['description']) &&
    isset($_POST['parent']) &&
    isset($_POST['rating'])) {
    ignore_user_abort(true);  // Prevent user closing the page from stopping the upload (so as to not corrupt SQL state).
    if (!(!isset($_FILES['file']['error']) || is_array($_FILES['file']['error']) || empty($_FILES['file']['name']))) {
        // Try file download.
        accept_file_upload($tmp_path) or OnFileUploadError(null);
    } else if (isset($_POST['source'])) {
        // Get file from url.
        $url = $_POST['source'];
        if (startsWith($url, "https://")) {
            // Replace with non-https.
            $url = "http://".mb_substr($url, 8);
        }
        $external_ext = GetExtensionFromURL($url);
        if ($external_ext == null) {
            RenderErrorPage("Error while uploading file: Invalid file extensions");
        }
        $tmp_path = time().".$external_ext";
        file_put_contents($tmp_path, fopen($url, 'r'));
    } else {
        RenderErrorPage("Error while uploading file: No file provided");
    }
    $md5 = md5_file($tmp_path);
    $ext = GetFileExtension($tmp_path);
    if ($ext == null) OnFileUploadError($tmp_path);
    $dst_path = GetSystemImagePath($md5, $ext);
    if (file_exists($dst_path)) {
        AddTagsToExistingPost($_POST['tags'], $md5);
        unlink($tmp_path);
        GoToExistingFile($md5);
        return;
    }
    mksysdirs(dirname($dst_path));
    rename($tmp_path, $dst_path);
    $filesize = GetHumanReadableFileSize(filesize($dst_path));
    switch ($ext) {
        case 'jpg':
        case 'png':
        case 'gif':
            $meta = getimagesize($dst_path);
            $width = $meta[0];
            $height = $meta[1];
            $thumb_path = CreateThumbnailFile($md5, $ext);
            $preview_path = CreatePreviewFile($md5, $ext);
            $has_preview = ($dst_path == $preview_path ? 0 : 1);
            break;
        case 'swf':
            $meta = getimagesize($dst_path);
            $width = $meta[0];
            $height = $meta[1];
            $thumb_path = "";
            $preview_path = "";
            $has_preview = 0;
            break;
        case 'webm':
            $getID3 = new getID3();
            $file = $getID3->analyze($dst_path);
            $width = $file['video']['resolution_x'];
            $height = $file['video']['resolution_y'];
            $thumb_path = "";
            $preview_path = "";
            $has_preview = 0;
            break;
        default:
            RenderErrorPage("Error while uploading file: Invalid file extension");
            break;
    }
    $uploader_id = $user['UserId'];
    // If correct parameter is set, don't include uploader id. This isn't in the normal web form, just a supported POST parameter for private API access.
    if (isset($_POST['hide_uploader_id']) && $_POST['hide_uploader_id'] == "1" && CanUserUploadWithHiddenUserId($user)) {
        $uploader_id = -1;
    }

    if (mb_strlen($_POST['source']) > 0) {
        $source = mb_substr(str_replace(" ", "%20", $_POST['source']), 0, 256);
    } else {
        $source = "";
    }
    $rating = $_POST['rating'];
    if (!($rating == 's' || $rating == 'q' || $rating == 'e')) {
        $rating = 'q';
    }
    if (mb_strlen($_POST['description']) > 0) {
        // TODO: Allow description formatting?
        $escaped_description = sql_escape(mb_substr($_POST['description'], 0, MAX_GALLERY_POST_DESCRIPTION_LENGTH));
    } else {
        $escaped_description = "";
    }
    $parent_post_id = GetValidParentPostId($_POST['parent'], -1);
    $status = CanUserUploadNonPending($user) ? "A" : "P";
    $now = time();
    $result = sql_query("INSERT INTO ".GALLERY_POST_TABLE."
        (Md5, Extension, HasPreview, UploaderId, DateUploaded, Width, Height, FileSize, Status)
        VALUES
        ('$md5', '$ext', $has_preview, $uploader_id, $now, $width, $height, '$filesize', '$status');");
    if (!$result) {
        // Error uploading file. Clean up final data file and thumbnail/preview files.
        // Thumbs won't exist for video/flash, but these are "".
        if ($has_preview) {
            OnFileUploadError(array($dst_path, $thumb_path, $preview_path));
        } else {
            OnFileUploadError(array($dst_path, $thumb_path));
        }
    }
    $post_id = sql_last_id();
    // Update post with tags history gets recorded.
    // Append rating and stuff before tags, so that tags can override the other fields.
    UpdatePost("rating:$rating source:$source parent:$parent_post_id ".$_POST['tags'], $post_id, $user);
    $log_description_change = mb_strlen($_POST['description']);
    UpdatePostDescription($post_id, $_POST['description'], $user, $log_description_change);
    $uid = $user['UserId'];
    $username = $user['DisplayName'];
    LogVerboseAction("<strong><a href='/user/$uid/'>$username</a></strong> uploaded <strong><a href='/gallery/post/show/$post_id/'>post #$post_id</a></strong>", "G");
    Redirect("/gallery/post/show/$post_id/");
}

RenderPage("gallery/upload.tpl");
return;

// Renders the appropriate error message, and deletes the temporary file.
function OnFileUploadError($tmp_paths, $msg = "Error while uploading file") {
    if (isset($tmp_paths) && $tmp_paths !== null) {
        if (is_array($tmp_paths)) {
            foreach ($tmp_paths as $path) {
                // The path could be "" if the file is not supposed to exist (e.g. video thumb).
                if (strlen($path) > 0) {
                    unlink($path);
                }
            }
        } else {
            unlink($tmp_paths);
        }
    }
    RenderErrorPage($msg);
}

function AddTagsToExistingPost($tag_string, $md5) {
    global $user;
    if (!CanUserEditGalleryPost($user)) return;
    if (sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE Md5='$md5';", 1)) {
        $post_id = $result->fetch_assoc()['PostId'];
        $existing_tags = ToTagNameString(GetTags($post_id));
        $all_tags = "$existing_tags $tag_string";
        UpdatePost($all_tags, $post_id, $user);
    }
}

function GoToExistingFile($md5) {
    sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE Md5='$md5';", 1) or RenderErrorPage("Error while uploading file.");
    $id = $result->fetch_assoc()['PostId'];
    Redirect("/gallery/post/show/$id/");
}

function GetExtensionFromURL($url) {
    $url_path = $url;
    if (contains($url_path, "?")) {
        $url_path = substr($url_path, 0, strpos($url_path, "?"));
    }
    if (contains($url_path, "#")) {
        $url_path = substr($url_path, 0, strpos($url_path, "?"));
    }
    return GetFileExtension($url_path);
}
?>