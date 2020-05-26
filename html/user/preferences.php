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
include_once(SITE_ROOT."includes/auth/email_auth.php");

include(SITE_ROOT."user/includes/profile_setup.php");
$profile_user = &$vars['profile']['user'];

if (!isset($user) || !CanUserEditBasicInfo($user, $profile_user)) {
    RenderErrorPage("Not authorized to edit this profile");
    return;
}

if (isset($_POST['display-name']) &&
    isset($_POST['dob']) &&
    isset($_POST['species']) &&
    isset($_POST['title']) &&
    isset($_POST['location']) &&
    isset($_POST['email']) &&
    isset($_POST['password']) &&
    isset($_POST['password-confirm']) &&
    isset($_POST['timezone']) &&
    isset($_POST['skin']) &&
    isset($_POST['signature']) &&
    isset($_POST['gallery-posts-per-page']) &&
    isset($_POST['gallery-tag-blacklist']) &&
    isset($_POST['fics-stories-per-page']) &&
    isset($_POST['fics-tag-blacklist'])) {
    if (!CanPerformSitePost()) MaintenanceError();
    $settings_changed = false;
    // Handle post submit.
    $user_table_sets = array();
    $uid = $profile_user['UserId'];
    // DisplayName
    if ($_POST['display-name'] != $profile_user['DisplayName']) {
        $display_name = $_POST['display-name'];  // Allow case-sensitive.
        $valid_name = true;
        // Okay to use strlen here, checking for database length.
        if (!(MIN_DISPLAY_NAME_LENGTH <= strlen($display_name) && strlen($display_name) <= MAX_DISPLAY_NAME_LENGTH)) {
            PostErrorMessage("Name must be between ".MIN_DISPLAY_NAME_LENGTH." and ".MAX_DISPLAY_NAME_LENGTH." characters");
            $valid_name = false;
        }
        if ($valid_name && !mb_ereg("^[A-Za-z0-9_]+$", $display_name)) {
            PostErrorMessage("Name must consist of letters, numbers or _");
            $valid_name = false;
        }
        if ($valid_name && !mb_ereg("^[A-Za-z][A-Za-z0-9_]+$", $display_name)) {
            PostErrorMessage("Name must start with a letter");
            $valid_name = false;
        }
        if ($valid_name && !IsValidDisplayName($display_name)) {
            PostErrorMessage("Invalid name");
            $valid_name = false;
        }
        $escaped_display_name = sql_escape(GetSanitizedTextTruncated($display_name, NO_HTML_TAGS, MAX_DISPLAY_NAME_LENGTH));
        // Check for duplicates.
        // Search for display name, and current user (so that at least one result is returned).
        if ($valid_name) {
            // Search for name or self.
            if (!sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE (UPPER(DisplayName)=UPPER('$escaped_display_name') AND ".ACCOUNT_NOT_IMPORTED_SQL_CONDITION.") OR UserId='$uid';", 1)) {
                PostErrorMessage("Failed to change name");
                $valid_name = false;
            } else {
                if ($valid_name && $result->num_rows != 1) {
                    PostErrorMessage("Name already taken!");
                    $valid_name = false;
                }
                $now = time();
                $row = $result->fetch_assoc();
                $last_change_time = (int)$row['DisplayNameChangeTime'];
                if ($valid_name && !CanUserQuickChangeName($user, $profile_user) && $now - $last_change_time < DISPLAY_NAME_CHANGE_TIME_LIMIT) {
                    PostErrorMessage("Can only change name once every ".DISPLAY_NAME_CHANGE_TIME_LIMIT_STR);
                    $valid_name = false;
                }
                if ($valid_name) {
                    $user_table_sets[] = "DisplayName='$escaped_display_name'";
                    $user_table_sets[] = "DisplayNameChangeTime=$now";
                    $username = $user['DisplayName'];
                    $newUsername = $display_name;
                    if ($user['UserId'] == $profile_user['UserId']) {
                        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> changed names to <strong>$newUsername</strong>", "");
                    } else {
                        $profile_username = $profile_user['DisplayName'];
                        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> changed the name of account <strong><a href='/user/$uid/'>$profile_username</a></strong> to <strong>$newUsername</strong>", "");
                    }
                }
            }
        }
    }
    // Gender
    $gender = $_POST['gender'];
    if ($gender == "male") {
        $gender = 'M';
    } else if ($gender == "female") {
        $gender = 'F';
    } else if ($gender == "other") {
        $gender = 'O';
    } else {
        $gender = 'U';
    }
    if ($gender != $profile_user['Gender']) {
        $user_table_sets[] = "Gender='$gender'";
    }
    // DOB
    if ($_POST['dob'] != $profile_user['DOB']) {
        $old_dob = $profile_user['DOB'];
        $dob = ParseDate($_POST['dob']);
        if ($dob) {
            $user_table_sets[] = "DOB='".sql_escape($dob)."'";
            $username = $user['DisplayName'];
            $log_dob = htmlspecialchars($_POST['dob']);
            LogAction("<strong><a href='/user/$uid/'>$username</a></strong> changed birthday from <strong>$old_dob</strong> to <strong>$log_dob</strong>", "");
        }
    }
    // ShowDOB
    $show_dob = isset($_POST['show-dob']);
    if ($show_dob != $profile_user['ShowDOB']) {
        $user_table_sets[] = "ShowDOB=".($show_dob ? "TRUE" : "FALSE");
    }
    // Species
    if ($_POST['species'] != $profile_user['Species']) {
        $escaped_species = sql_escape(GetSanitizedTextTruncated($_POST['species'], NO_HTML_TAGS, MAX_USER_SPECIES_LENGTH));
        $user_table_sets[] = "Species='$escaped_species'";
    }
    // Title
    if ($_POST['title'] != $profile_user['Title']) {
        $escaped_title = sql_escape(GetSanitizedTextTruncated($_POST['title'], NO_HTML_TAGS, MAX_USER_TITLE_LENGTH));
        $user_table_sets[] = "Title='$escaped_title'";
    }
    // Location
    if ($_POST['location'] != $profile_user['Location']) {
        $escaped_location = sql_escape(GetSanitizedTextTruncated($_POST['location'], NO_HTML_TAGS, MAX_USER_LOCATION_LENGTH));
        $user_table_sets[] = "Location='$escaped_location'";
    }
    // Timezone
    if (isset($_POST['auto-detect-timezone'])) {
        if ($user['AutoDetectTimezone'] != 1) {
            $user_table_sets[] = "AutoDetectTimezone=1";
        }
    } else {
        if ($user['AutoDetectTimezone'] != 0) {
            $user_table_sets[] = "AutoDetectTimezone=0";
        }
        $timezone = ParseGMTTimeZoneToFloat($_POST['timezone']);
        if ($timezone != null && $timezone != $profile_user['Timezone']) {
            $user_table_sets[] = "Timezone=$timezone";
        }
    }
    // Show Local time
    if (isset($_POST['show-local-time'])) {
        if ($user['ShowLocalTime'] != 1) {
            $user_table_sets[] = "ShowLocalTime=1";
        }
    } else {
        if ($user['ShowLocalTime'] != 0) {
            $user_table_sets[] = "ShowLocalTime=0";
        }
    }
    // Handle avatar.
    ProcessAvatarUpload($user_table_sets);
    // GroupMailboxThreads
    $group_mailbox = isset($_POST['group-pm']);
    if ($group_mailbox != $profile_user['GroupMailboxThreads']) {
        $user_table_sets[] = "GroupMailboxThreads=".($group_mailbox ? "TRUE" : "FALSE");
    }
    // HideOnlineStatus
    $hide_online = isset($_POST['hide-online']);
    if ($hide_online != $profile_user['HideOnlineStatus']) {
        $user_table_sets[] = "HideOnlineStatus=".($hide_online ? "TRUE" : "FALSE");
    }
    // Skin
    if ($_POST['skin'] != $profile_user['Skin']) {
        if (contains($_POST['skin'], ".") || contains($_POST['skin'], "/") || contains($_POST['skin'], "\\") || !in_array($_POST['skin'], $vars['availableSkins'])) {
            PostErrorMessage("Invalid site skin");
        } else {
            $escaped_skin = sql_escape(GetSanitizedTextTruncated($_POST['skin'], NO_HTML_TAGS, MAX_SKIN_STRING_LENGTH));
            $user_table_sets[] = "Skin='$escaped_skin'";
        }
    }
    if (sizeof($user_table_sets) > 0) {
        sql_query("UPDATE ".USER_TABLE." SET ".implode(", ", $user_table_sets)." WHERE UserId=".$profile_user['UserId'].";");
        $settings_changed = true;
    }
    // Email/Password.
    $stop_change_email_password = false;
    $email = $profile_user['Email'];
    $pass = $profile_user['Password'];
    if ($_POST['email'] != $email) {
        if (ValidateEmail($_POST['email'])) {
            $email = $_POST['email'];
        } else {
            PostErrorMessage("Invalid email address, Email/Password not changed");
            $stop_change_email_password = true;
        }
    }
    if (mb_strlen($_POST['password']) > 0) {
        if ($_POST['password'] != $_POST['password-confirm']) {
            PostErrorMessage("Passwords do not match, Email/Password not changed");
            $stop_change_email_password = true;
        } else if (mb_strlen($_POST['password']) < MIN_PASSWORD_LENGTH) {
            PostErrorMessage("Password must be at least ".MIN_PASSWORD_LENGTH." characters, Email/Password not changed");
            $stop_change_email_password = true;
        } else {
            $pass = md5($_POST['password']);
        }
    }
    $email_changed = $email != $profile_user['Email'];
    $pass_changed = $pass != $profile_user['Password'];
    if (!$stop_change_email_password && ($email_changed || $pass_changed)) {
        if ($email_changed && $pass_changed) {
            $detailed_desc = "email and password";
        } else if ($email_changed) {
            $detailed_desc = "email";
        } else if ($pass_changed) {
            $detailed_desc = "password";
        }
        // Allow unverified changes for administrators, if they're not changing their own account (for security reasons).
        $old_email = $profile_user['Email'];
        if ($user['UserId'] != $profile_user['UserId'] && CanUserChangeEmailAndPasswordWithoutVerification($user, $profile_user)) {
            $uuid = $user['UserId'];
            $username = $user['DisplayName'];
            $puid = $uid;
            $pusername = $profile_user['DisplayName'];
            LogAction("<strong><a href='/user/$uuid/'>$username</a></strong> forced an email/password change for account <strong><a href='/user/$puid/'>$pusername</a></strong>", "");
            ChangeEmailPassword($profile_user['UserId'], $email, $pass, false /* confirmation email */, false /* force login */);
            PostConfirmMessage("User $detailed_desc changed");
        } else if (mb_strlen($old_email) == 0) {
            // Previous email was not set, allow changing email/password (valid email is provided by this point).
            $uuid = $user['UserId'];
            $username = $user['DisplayName'];
            $puid = $uid;
            $pusername = $profile_user['DisplayName'];
            LogAction("<strong><a href='/user/$uuid/'>$username</a></strong> updated their email/password from imported empty value", "");
            ChangeEmailPassword($profile_user['UserId'], $email, $pass, false /* confirmation email */, false /* force login */);
            PostConfirmMessage("User $detailed_desc changed");
        } else {
            // Redirect to: /recover/success/
            $username = $profile_user['UserName'];
            $redirect = "/user/auth/change/";
            debug("Pass:$pass, Post=".$profile_user['UserId'].",".$email.",".$pass);
            $code = CreateCodeEntry($old_email, "account_auth_change", $profile_user['UserId'].",".$email.",".$pass, $redirect);
            if ($code !== FALSE) {
                if (SendRecoveryEmail($old_email, $username, $email_changed, $pass_changed, $code)) {
                    debug("Email sent, with uid=".$profile_user['UserId'].", email=".$email.", pass_md5=".$pass);
                    $uuid = $user['UserId'];
                    $username = $user['DisplayName'];
                    $puid = $uid;
                    $pusername = $profile_user['DisplayName'];
                    LogAction("<strong><a href='/user/$uuid/'>$username</a></strong> requested an email/password change", "");
                    PostConfirmMessage("To finish changing your $detailed_desc, please click the link in the email sent to ".$profile_user['Email']." (may need to check your spam folder)");
                } else {
                    $vars['error'] = "Error sending confirmation email, please try again later";
                }
            } else {
                PostErrorMessage("Failed to change email/password");
            }
        }
    } else {
        // The user did not change email or password.
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
        $escaped_signature = sql_escape(GetSanitizedTextTruncated($signature, DEFAULT_ALLOWED_TAGS, MAX_FORUMS_SIGNATURE_LENGTH));
        $forums_table_sets[] = "Signature='$escaped_signature'";
    }
    if (sizeof($forums_table_sets) > 0) {
        sql_query("UPDATE ".FORUMS_USER_PREF_TABLE." SET ".implode(", ", $forums_table_sets)." WHERE UserId=".$profile_user['UserId'].";");
        $settings_changed = true;
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
    // Ignore blacklist in pools.
    if (isset($_POST['gallery-ignore-blacklist-for-pools'])) {
        if (!$profile_user['IgnoreGalleryBlacklistForPools']) {
            $gallery_table_sets[] = "IgnoreGalleryBlacklistForPools=TRUE";
        }
    } else {
        if ($profile_user['IgnoreGalleryBlacklistForPools']) {
            $gallery_table_sets[] = "IgnoreGalleryBlacklistForPools=FALSE";
        }
    }
    // Gallery enable keyboard navigation
    if (isset($_POST['gallery-enable-keyboard'])) {
        if (!$profile_user['NavigateGalleryPoolsWithKeyboard']) {
            $gallery_table_sets[] = "NavigateGalleryPoolsWithKeyboard=TRUE";
        }
    } else {
        if ($profile_user['NavigateGalleryPoolsWithKeyboard']) {
            $gallery_table_sets[] = "NavigateGalleryPoolsWithKeyboard=FALSE";
        }
    }
    // Gallery plain tagging UI.
    if (isset($_POST['gallery-plain-tagging'])) {
        if (!$profile_user['PlainGalleryTagging']) {
            $gallery_table_sets[] = "PlainGalleryTagging=TRUE";
        }
    } else {
        if ($profile_user['PlainGalleryTagging']) {
            $gallery_table_sets[] = "PlainGalleryTagging=FALSE";
        }
    }
    // Gallery hide favorites
    if (isset($_POST['gallery-hide-favorites'])) {
        if (!$profile_user['PrivateGalleryFavorites']) {
            $gallery_table_sets[] = "PrivateGalleryFavorites=TRUE";
        }
    } else {
        if ($profile_user['PrivateGalleryFavorites']) {
            $gallery_table_sets[] = "PrivateGalleryFavorites=FALSE";
        }
    }
    if (sizeof($gallery_table_sets) > 0) {
        sql_query("UPDATE ".GALLERY_USER_PREF_TABLE." SET ".implode(", ", $gallery_table_sets)." WHERE UserId=".$profile_user['UserId'].";");
        $settings_changed = true;
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
    // Fics plain tagging UI.
    if (isset($_POST['fics-plain-tagging'])) {
        if (!$profile_user['PlainFicsTagging']) {
            $fics_table_sets[] = "PlainFicsTagging=TRUE";
        }
    } else {
        if ($profile_user['PlainFicsTagging']) {
            $fics_table_sets[] = "PlainFicsTagging=FALSE";
        }
    }
    // Fics hide favorites
    if (isset($_POST['fics-hide-favorites'])) {
        if (!$profile_user['PrivateFicsFavorites']) {
            $fics_table_sets[] = "PrivateFicsFavorites=TRUE";
        }
    } else {
        if ($profile_user['PrivateFicsFavorites']) {
            $fics_table_sets[] = "PrivateFicsFavorites=FALSE";
        }
    }
    if (sizeof($fics_table_sets) > 0) {
        sql_query("UPDATE ".FICS_USER_PREF_TABLE." SET ".implode(", ", $fics_table_sets)." WHERE UserId=".$profile_user['UserId'].";");
        $settings_changed = true;
    }

    // TODO: Save Oekaki settings.

    // Resend cookie set.
    // Reload profile with new settings.
    include(SITE_ROOT."user/includes/profile_setup.php");
    // Show error/confirmation banner.
    if ($settings_changed) PostConfirmMessage("Settings saved");

    // Redirect user.
    Redirect("/user/$uid/preferences/");
}

/////////////////////////////////////////
// Initialize normal preferences page. //
/////////////////////////////////////////

$profile_user['timezoneOffset'] = GetGMTTimeZone($profile_user['Timezone']);
$profile_user['admin'] = GetAdminBadge($profile_user);
if ($profile_user['Skin'] == DEFAULT_SKIN_SETTING) {
    $profile_user['skin'] = DEFAULT_SKIN;
} else {
    $profile_user['skin'] = $profile_user['Skin'];
}

// Set up tagging info for blacklists.
// Gallery.
$tags = explode(" ", $profile_user['GalleryTagBlacklist']);
$tag_matches = implode(" OR ", array_map(function($name) { return "(Name='$name')"; }, $tags));
if (sql_query_into($result, "SELECT * FROM ".GALLERY_TAG_TABLE." WHERE $tag_matches ORDER BY Type DESC, Name DESC;", 1)) {
    $tags = array();
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
    $vars['gallery_blacklist_tags'] = $tags;
}
// Fics.
$tags = explode(" ", $profile_user['FicsTagBlacklist']);
$tag_matches = implode(" OR ", array_map(function($name) { return "(Name='$name')"; }, $tags));
if (sql_query_into($result, "SELECT * FROM ".FICS_TAG_TABLE." WHERE $tag_matches ORDER BY Type DESC, Name DESC;", 1)) {
    $tags = array();
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
    $vars['fics_blacklist_tags'] = $tags;
}
 
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
    PostSessionBanner($msg, "red");
}

function PostConfirmMessage($msg) {
    PostSessionBanner($msg, "green");
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
    $ret .= sprintf("%+03d", (int)($timezone_float));
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
        $fname = "user$uid.".time().".".AVATAR_UPLOAD_EXTENSION;
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