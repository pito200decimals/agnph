<?php
// Account preferences page. Includes basic info editing.
// URL: /user/{user-id}/preferences/
// URL: /user/preferences.php?uid={user-id}

include_once("../header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/date.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."../lib/getid3/getid3.php");

include(SITE_ROOT."user/includes/profile_setup.php");
$profile_user = &$vars['profile']['user'];

$vars['banner_nofications'] = array();

if (isset($_POST['display-name']) &&
    isset($_POST['dob']) &&
    isset($_POST['species']) &&
    isset($_POST['title']) &&
    isset($_POST['location']) &&
    isset($_POST['email']) &&
    isset($_POST['password']) &&
    isset($_POST['passwordconfirm']) &&
    isset($_POST['timezone']) &&
    isset($_POST['signature']) &&
    isset($_POST['gallery-posts-per-page']) &&
    isset($_POST['gallery-tag-blacklist']) &&
    isset($_POST['fics-stories-per-page']) &&
    isset($_POST['fics-tag-blacklist']) &&
    isset($_POST['oekaki-posts-per-page'])) {
    if (!isset($user) || !CanUserEditBasicInfo($user, $profile_user)) {
        RenderErrorPage("Not authorized to edit this profile");
        return;
    }
    // Handle post submit.
    $user_table_sets = array();
    // DisplayName
    if ($_POST['display-name'] != $profile_user['DisplayName']) {
        $uid = $profile_user['UserId'];
        $display_name = $_POST['display-name'];
        $display_name = mb_ereg_replace("[^a-zA-Z0-9_.-]", "", $display_name);
        $escaped_display_name = sql_escape($display_name);
        // Check for duplicates.
        // Search for display name, and current user (so that at least one result is returned).
        if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE DisplayName='$escaped_display_name' OR UserId='$uid';", 1)) {
            if ($result->num_rows == 1) {
                // TODO: Check for too many changes.
                // TODO: Log change.
                $user_table_sets[] = "DisplayName='$escaped_display_name'";
            } else {
                PostErrorMessage("Name already taken!");
            }
        } else {
            PostErrorMessage("Failed to change Name");
        }
    }
    // DOB
    if ($_POST['dob'] != $profile_user['DOB']) {
        $dob = ParseDate($_POST['dob']);
        if ($dob) {
            $user_table_sets[] = "DOB='".sql_escape($dob)."'";
        }
    }
    // ShowDOB
    $show_dob = isset($_POST['show-dob']);
    if ($show_dob != $profile_user['ShowDOB']) {
        $user_table_sets[] = "ShowDOB=".($show_dob ? "TRUE" : "FALSE");
    }
    // Species
    if ($_POST['species'] != $profile_user['Species']) {
        $escaped_species = sql_escape($_POST['species']);
        $user_table_sets[] = "Species='$escaped_species'";
    }
    // Title
    if ($_POST['title'] != $profile_user['Title']) {
        $escaped_title = sql_escape($_POST['title']);
        $user_table_sets[] = "Title='$escaped_title'";
    }
    // Location
    if ($_POST['location'] != $profile_user['Location']) {
        $escaped_location = sql_escape($_POST['location']);
        $user_table_sets[] = "Location='$escaped_location'";
    }
    // TODO: Email
    // TODO: Password
    // Timezone
    $timezone = ParseGMTTimeZoneToFloat($_POST['timezone']);
    if ($timezone != null && $timezone != $profile_user['Timezone']) {
        $user_table_sets[] = "Timezone=$timezone";
    }
    // GroupMailboxThreads
    $group_mailbox = isset($_POST['group-pm']);
    if ($group_mailbox != $profile_user['GroupMailboxThreads']) {
        $user_table_sets[] = "GroupMailboxThreads=".($group_mailbox ? "TRUE" : "FALSE");
    }
    // Handle avatar.
    ProcessAvatarUpload($user_table_sets);
    if (sizeof($user_table_sets) > 0) {
        sql_query("UPDATE ".USER_TABLE." SET ".implode(", ", $user_table_sets)." WHERE UserId=".$profile_user['UserId'].";");
    }

    $forums_table_sets = array();
    // ForumThreadsPerPage
    if ($_POST['forums-threads-per-page'] != $profile_user['ForumThreadsPerPage']) {
        $posts = $_POST['forums-posts-per-page'];
        if (is_numeric($posts)) {
            $posts = (int)$posts;
            if ($posts > 0 && $posts < MAX_FORUMS_THREADS_PER_PAGE) {
                $forums_table_sets[] = "ForumThreadsPerPage=$posts";
            }
        }
    }
    // ForumPostsPerPage
    if ($_POST['forums-posts-per-page'] != $profile_user['ForumPostsPerPage']) {
        $posts = $_POST['forums-posts-per-page'];
        if (is_numeric($posts)) {
            $posts = (int)$posts;
            if ($posts > 0 && $posts < MAX_FORUMS_POSTS_PER_PAGE) {
                $forums_table_sets[] = "ForumPostsPerPage=$posts";
            }
        }
    }
    // Signature
    // Purposefully do non-utf-8 substr here, to ensure all of the output can be saved in database.
    $signature = SanitizeHTMLTags(substr($_POST['signature'], 0, MAX_FORUMS_SIGNATURE_LENGTH), DEFAULT_ALLOWED_TAGS);
    if ($signature !== $profile_user['Signature']) {
        $escaped_signature = sql_escape($signature);
        $forums_table_sets[] = "Signature='$escaped_signature'";
    }
    if (sizeof($forums_table_sets) > 0) {
        sql_query("UPDATE ".FORUMS_USER_PREF_TABLE." SET ".implode(", ", $forums_table_sets)." WHERE UserId=".$profile_user['UserId'].";");
    }

    $gallery_table_sets = array();
    // GalleryPostsPerPage
    if ($_POST['gallery-posts-per-page'] != $profile_user['GalleryPostsPerPage']) {
        $posts = $_POST['gallery-posts-per-page'];
        if (is_numeric($posts)) {
            $posts = (int)$posts;
            if ($posts > 0) {
                if ($posts > MAX_GALLERY_POSTS_PER_PAGE) {
                    $posts = MAX_GALLERY_POSTS_PER_PAGE;
                }
                $gallery_table_sets[] = "GalleryPostsPerPage=$posts";
            }
        }
    }
    // GalleryTagBlacklist
    if ($_POST['gallery-tag-blacklist'] != $profile_user['GalleryTagBlacklist']) {
        $escaped_blacklist = sql_escape($_POST['gallery-tag-blacklist']);
        $gallery_table_sets[] = "GalleryTagBlacklist='$escaped_blacklist'";
    }
    if (isset($_POST['gallery-enable-keyboard'])) {
        if (!$profile_user['NavigateGalleryPoolsWithKeyboard']) {
            $gallery_table_sets[] = "NavigateGalleryPoolsWithKeyboard=TRUE";
        }
    } else {
        if ($profile_user['NavigateGalleryPoolsWithKeyboard']) {
            $gallery_table_sets[] = "NavigateGalleryPoolsWithKeyboard=FALSE";
        }
    }
    if (sizeof($gallery_table_sets) > 0) {
        sql_query("UPDATE ".GALLERY_USER_PREF_TABLE." SET ".implode(", ", $gallery_table_sets)." WHERE UserId=".$profile_user['UserId'].";");
    }


    $fics_table_sets = array();
    // FicsStoriesPerPage
    if ($_POST['fics-stories-per-page'] != $profile_user['FicsStoriesPerPage']) {
        $posts = $_POST['fics-stories-per-page'];
        if (is_numeric($posts)) {
            $posts = (int)$posts;
            if ($posts > 0) {
                if ($posts > MAX_FICS_POSTS_PER_PAGE) {
                    $posts = MAX_FICS_POSTS_PER_PAGE;
                }
                $fics_table_sets[] = "FicsStoriesPerPage=$posts";
            }
        }
    }
    // FicsTagBlacklist
    if ($_POST['fics-tag-blacklist'] != $profile_user['FicsTagBlacklist']) {
        $escaped_blacklist = sql_escape($_POST['fics-tag-blacklist']);
        $fics_table_sets[] = "FicsTagBlacklist='$escaped_blacklist'";
    }
    if (sizeof($fics_table_sets) > 0) {
        sql_query("UPDATE ".FICS_USER_PREF_TABLE." SET ".implode(", ", $fics_table_sets)." WHERE UserId=".$profile_user['UserId'].";");
    }

    // TODO: Save Oekaki settings.

    // Resend cookie set.
    // Reload profile with new settings.
    include(SITE_ROOT."user/includes/profile_setup.php");
    // Also grab $user again, since $user may not be equal to $profile_user.
    LoadAllUserPreferences($user['UserId'], $user, true/*fresh*/);
    
    // Show error/confirmation banner.
    PostConfirmMessage("Settings saved");
}

/////////////////////////////////////////
// Initialize normal preferences page. //
/////////////////////////////////////////

$profile_user['timezoneOffset'] = GetGMTTimeZone($profile_user['Timezone']);
$profile_user['admin'] = GetAdminBadge($profile_user);

// Init private visible statistics.
if (isset($user)) {
    $vars['canEditBio'] = CanUserEditBio($user, $profile_user);
    $vars['canEditBasicInfo'] = CanUserEditBasicInfo($user, $profile_user);
    $vars['canSeePrivateInfo'] = CanUserSeePrivateInfo($user, $profile_user);
    $vars['canSeeAdminInfo'] = CanUserSeeAdminInfo($user);
    if (mb_strlen($profile_user['RegisterIP']) == 0 || mb_strpos($profile_user['KnownIPs'], $profile_user['RegisterIP']) !== FALSE) {
        $profile_user['ips'] = $profile_user['KnownIPs'];
    } else {
        $profile_user['ips'] = $profile_user['RegisterIP'].",".$profile_user['KnownIPs'];
    }
}

// This is how to output the template.
RenderPage("user/preferences.tpl");
return;

function PostErrorMessage($msg) {
    global $vars;
    $vars['banner_nofications'][] = array(
        "classes" => array("red-banner"),
        "text" => $msg,
        "dismissable" => true,
        "strong" => true);
}

function PostConfirmMessage($msg) {
    global $vars;
    $vars['banner_nofications'][] = array(
        "classes" => array("green-banner"),
        "text" => $msg,
        "dismissable" => true,
        "strong" => true);
}

// Returns the valid date string, or false on parse error.
function ValidateDateString($date_str) {
    // Must be in the format "MM/DD/YYYY"
    $split = mb_split("/", $date_str);
    if (sizeof($split) != 3) return false;
    if (!is_numeric($split[0])) return false;
    if (!is_numeric($split[1])) return false;
    if (!is_numeric($split[2])) return false;
    $month = (int)$split[0];
    if ($month <= 0 || $month > 12) return false;
    $day = (int)$split[1];
    if ($month == 1 || $month == 3 || $month == 5 || $month == 7 || $month == 8 || $month == 10 || $month == 12) {
        if ($day <= 0 || $day > 31) return false;
    } else if ($month == 4 || $month == 6 || $month == 9 || $month == 11) {
        if ($day <= 0 || $day > 30) return false;
    } else {
        if ($day <= 0 || $day > 29) return false;
    }
    $year = (int)$split[2];
    if ($year <= 1900) return false;
    if ($year > 2100) return false;
    return sprintf("%02d/%02d/%04d", $month, $day, $year);
}

// Gets a human-readable GMT timezone offset from the database float offset.
function GetGMTTimeZone($timezone_float) {
    $ret = "GMT ";
    $ret .= ($timezone_float >= 0 ? "+" : "");
    $ret .= sprintf("%02d", (int)($timezone_float));
    $remainder = $timezone_float - (int)($timezone_float);
    $ret .= sprintf("%02d", (int)($remainder * 60));
    return $ret;
}

// Returns the timezone float, or null if a parsing error occurs.
function ParseGMTTimeZoneToFloat($gmt) {
    if (mb_strlen($gmt) != 9) return null;
    if (mb_substr($gmt, 0, 4) != "GMT ") return null;
    if (mb_substr($gmt, 4, 1) == "+") {
        $sign = 1;
    } else if (mb_substr($gmt, 4, 1) == "-") {
        $sign = -1;
    } else {
        return null;
    }
    $hours = mb_substr($gmt, 5, 2);
    $minutes = mb_substr($gmt, 7, 2);
    if (!is_numeric($hours) || !is_numeric($minutes)) return null;
    $offset = $hours + ((float)$minutes) / 60;
    return $sign * $offset;
}

function ProcessAvatarUpload(&$user_table_sets) {
    global $vars, $profile_user;
    if (isset($_POST['reset-avatar'])) {
        // Reset to default.
        if ($profile_user['AvatarPostId'] != -1)
            $user_table_sets[] = "AvatarPostId=-1";
        if ($profile_user['AvatarFname'] != "")
            $user_table_sets[] = "AvatarFname=''";
        // Delete old file, if it existed.
        $fname = $profile_user['AvatarFname'];
        if (strlen($fname)) {
            $path = SITE_ROOT."images/uploads/avatars/$fname";
            unlink($path);
        }
    } else if (!(!isset($_FILES['file']['error']) || is_array($_FILES['file']['error']) || empty($_FILES['file']['name']))) {
        if (!accept_file_upload($tmp_path)) {
            PostErrorMessage("Failed to upload avatar");
            return;
        }
        $md5 = md5_file($tmp_path);
        $ext = GetFileExtension($tmp_path);
        if ($ext == null) {
            PostErrorMessage("Failed to upload avatar");
            unlink($tmp_path);
            return;
        }
        if (!($ext == "jpg" || $ext == "png" || $ext == "gif")) {
            PostErrorMessage("Uploaded avatar must be .jpg, .png or .gif");
            unlink($tmp_path);
            return;
        }
        $uid = $profile_user['UserId'];
        $fname = "user$uid.".AVATAR_UPLOAD_EXTENSION;
        $dst_path = SITE_ROOT."images/uploads/avatars/$fname";
        // Check file properties.
        $meta = getimagesize($tmp_path);
        $width = $meta[0];
        $height = $meta[1];
        // Resize down to thumb size, and/or change file extension to jpg.
        $image = new SimpleImage();
        $image->load($tmp_path);
        // Always create thumbnail file.
        if ($image->getWidth() > $image->getHeight()) {
            if ($image->getWidth() > MAX_AVATAR_UPLOAD_DIMENSIONS)
                $image->resizeToWidth(MAX_AVATAR_UPLOAD_DIMENSIONS);
        } else {
            if ($image->getHeight() > MAX_AVATAR_UPLOAD_DIMENSIONS)
                $image->resizeToHeight(MAX_AVATAR_UPLOAD_DIMENSIONS);
        }
        $image->save($dst_path);
        unlink($tmp_path);

        $user_table_sets[] = "AvatarPostId=-1";
        $escaped_fname = sql_escape($fname);
        $user_table_sets[] = "AvatarFname='$escaped_fname'";
    }
}
?>