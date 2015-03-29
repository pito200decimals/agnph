<?php
// Page for uploading a post. Handles both upload form and the upload itself
// (which redirects to the post page after success).

include_once("../header.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."gallery/includes/functions.php");

if (!isset($user)) {
    RenderErrorPage("You must be logged in to upload a post.");
    return;
}
if (!CanUserUploadPost($user)) {
    RenderErrorPage("You are not authorized to upload a new posts.");
    return;
}

if ((!(!isset($_FILES['file']['error']) || is_array($_FILES['file']['error']) || empty($_FILES['file']['name'])) || isset($_POST['source'])) &&
    isset($_POST['tags']) &&
    isset($_POST['description']) &&
    isset($_POST['parent']) &&
    isset($_POST['rating'])) {
    if (!(!isset($_FILES['file']['error']) || is_array($_FILES['file']['error']) || empty($_FILES['file']['name']))) {
        // Try file download.
        accept_file_upload($tmp_path) or OnFileUploadError(null);
    } else if (isset($_POST['source'])) {
        // Get file from url.
        $url = $_POST['source'];
        $external_ext = GetFileExtension($url);
        if ($external_ext == null) {
            RenderErrorPage("Error while uploading file.");
        }
        $tmp_path = time().".$external_ext";
        file_put_contents($tmp_path, fopen($url, 'r'));
    } else {
        RenderErrorPage("Error while uploading file.");
    }
    $md5 = md5_file($tmp_path);
    $ext = GetFileExtension($tmp_path);
    if ($ext == null) OnFileUploadError($tmp_path);
    $dst_path = GetSystemImagePath($md5, $ext);
    if (file_exists($dst_path)) {
        unlink($tmp_path);
        GoToExistingFile($md5);
        return;
    }
    mksysdirs(dirname($dst_path));
    rename($tmp_path, $dst_path);
    $meta = getimagesize($dst_path);
    $width = $meta[0];
    $height = $meta[1];
    $filesize = GetHumanReadableFileSize(filesize($dst_path));
    $thumb_path = CreateThumbnailFile($md5, $ext);
    $preview_path = CreatePreviewFile($md5, $ext);
    $has_preview = ($dst_path == $preview_path ? 0 : 1);
    $uploader_id = $user['UserId'];

    if (strlen($_POST['source']) > 0) {
        $source = substr(str_replace(" ", "%20", $_POST['source']), 0, 256);
    } else {
        $source = "";
    }
    $rating = $_POST['rating'];
    if (!($rating == 's' || $rating == 'q' || $rating == 'e')) {
        $rating = 'q';
    }
    if (strlen($_POST['description']) > 0) {
        $escaped_description = sql_escape(substr($_POST['description'], 0, 512));
    } else {
        $escaped_description = "";
    }
    $parent_post_id = GetValidParentPostId($_POST['parent'], -1);
    // TODO: Have admins upload with non-pending status.
    $status = CanUserUploadNonPending($user) ? "A" : "P";
    $result = sql_query("INSERT INTO ".GALLERY_POST_TABLE."
        (Md5, Extension, HasPreview, UploaderId, Description, Width, Height, FileSize, Status)
        VALUES
        ('$md5', '$ext', $has_preview, $uploader_id, '$escaped_description', $width, $height, '$filesize', '$status');");
    if (!$result) {
        // Error uploading file.
        if ($has_preview) {
            OnFileUploadError(array($dst_path, $thumb_path, $preview_path));
        } else {
            OnFileUploadError(array($dst_path, $thumb_path));
        }
    }
    $post_id = sql_last_id();
    // Append rating and stuff before tags, so that tags can override the other fields.
    UpdatePost("rating:$rating source:$source parent:$parent_post_id ".$_POST['tags'], $post_id, $user);
    header("Location: /gallery/post/show/$post_id/");
    return;
}

RenderPage("gallery/upload.tpl");
return;

function OnFileUploadError($tmp_paths, $msg = "Error while uploading file.") {
    if (isset($tmp_paths) && $tmp_paths !== null) {
        if (is_array($tmp_paths)) {
            foreach ($tmp_paths as $path) {
                unlink($path);
            }
        } else {
            unlink($tmp_paths);
        }
    }
    RenderErrorPage($msg);
}

function GoToExistingFile($md5) {
    sql_query_into($result, "SELECT * FROM ".GALLERY_POST_TABLE." WHERE Md5='$md5';", 1) or RenderErrorPage("Error while uploading file.");
    $id = $result->fetch_assoc()['PostId'];
    header("Location: /gallery/post/show/$id/");
    return;
}
?>