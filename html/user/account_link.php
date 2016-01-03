<?php
// Page for handling account linking.
// URL: /user/account/link/ => account_link.php

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."user/includes/functions.php");
include_once(SITE_ROOT."gallery/includes/functions.php");
include_once(SITE_ROOT."fics/includes/functions.php");

if (!isset($user)) {
    RenderErrorPage("Must be logged in to access this page");
}

if (isset($_POST['service'])) {
    HandlePost();
    // If this returned, we have an error.
    err("Unexpected error, please try again");
}

if (strlen($user['Email'])) {
    $vars['similar_accounts'] = implode(", ", GetSimilarAccounts($user['Email']));
}

// This is how to output the template.
RenderPage("user/account_link.tpl");
return;

function err($msg) {
    PostSessionBanner($msg, "red");
    Redirect($_SERVER['REQUEST_URI']);
}

function success($msg) {
    PostSessionBanner($msg, "green");
    Redirect($_SERVER['REQUEST_URI']);
}

function HandlePost() {
    if (!CanPerformSitePost()) MaintenanceError();
    $service = $_POST['service'];
    switch ($service) {
        case "forums":
            ProcessPost("SMF_sha", $service, "ImportForumsPassword");
            break;
        case "gallery":
            ProcessPost(function($u, $p) { return sha1(GALLERY_CRYPT_SALT."--$p--"); }, $service, "ImportGalleryPassword");
            break;
        case "fics":
            ProcessPost(function ($u, $p) { return md5($p); }, $service, "ImportFicsPassword");
            break;
        case "oekaki":
            ProcessPost(function($u, $p) { return crypt($p, OEKAKI_CRYPT_SALT); }, $service, "ImportOekakiPassword");
            break;
        default:
            return;
    }
}

function ProcessPost($hash_fn, $section, $field) {
    if (isset($_POST["$section-username"]) && isset($_POST["$section-password"])) {
        // Password is simply md5-hashed in database.
        $username = $_POST["$section-username"];
        $escaped_username = sql_escape(IMPORTED_ACCOUNT_USERNAME_PREFIX.$username);
        $password = $_POST["$section-password"];
        $hashed_password = $hash_fn($username, $password);
        if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UPPER(Username)=UPPER('$escaped_username') AND RegisterIP='';", 1)) {
            while ($old_user = $result->fetch_assoc()) {
                if ($hashed_password == $old_user[$field]) {
                    $uid_to_migrate = $old_user['UserId'];
                } else if (md5($password) == $old_user['Password']) {  // Also allow this so admins can reset imported accounts' passwords if necessary.
                    // Note: Normal accounts are protected because they don't have the correct prefix.
                    $uid_to_migrate = $old_user['UserId'];
                } else {
                    // Failure, try another account with the same username if it exists.
                    continue;
                }
                $other_users = MigrateAccount($old_user['UserId']);
                if (sizeof($other_users) > 0) {
                    $msg = "Other accounts you might want to migrate: ".implode(", ", $other_users);
                    PostSessionBanner($msg, "green");
                }
                success("Account linked successfully");
            }
            err("Invalid password");
        } else {
            err(ucfirst($section)." account not found");
        }
    } else {
        // Invalid parameters, return and raise error later.
    }
}

function GetSimilarAccounts($email, $ignore_uid = -1) {
    $ret_array = array();
    $escaped_email = sql_escape($email);
    if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId<>$ignore_uid AND Email='$escaped_email' AND RegisterIP='';", 1)) {
        while ($row = $result->fetch_assoc()) {
            if (startsWith($row['UserName'], IMPORTED_ACCOUNT_USERNAME_PREFIX)) {
                $username = substr($row['UserName'], strlen(IMPORTED_ACCOUNT_USERNAME_PREFIX));
                $sections = array();
                if (strlen($row['ImportForumsPassword']) > 0) $sections[] = "Forums";
                if (strlen($row['ImportGalleryPassword']) > 0) $sections[] = "Gallery";
                if (strlen($row['ImportFicsPassword']) > 0) $sections[] = "Fics";
                if (strlen($row['ImportOekakiPassword']) > 0) $sections[] = "Oekaki";
                if (sizeof($sections) > 0) $username .= "(".implode(",", $sections).")";
                $ret_array[] = $username;
            }
        }
    }
    return $ret_array;
}

// TODO: Handle oekaki data.
// Migrates an account. Returns an array of similar usernames, if any were found (same email).
function MigrateAccount($uid) {
    global $user;
    $new_uid = $user['UserId'];
    LoadSingleTableEntry(array(USER_TABLE), "UserId", $uid, $old_user);
    if (!isset($old_user)) RenderErrorPage("Account not found");
    // Find same emails.
    $escaped_email = sql_escape($old_user['Email']);
    $ret_array = GetSimilarAccounts($old_user['Email'], $uid);

    $update_mapping = array(
        SITE_LOGGING_TABLE => "UserId",
        FORUMS_POST_TABLE => "UserId",
        GALLERY_POST_TAG_HISTORY_TABLE => "UserId",  // Likely empty.
        GALLERY_DESC_HISTORY_TABLE => "UserId",  // Likely empty.
        GALLERY_COMMENT_TABLE => "UserId",  // Likely empty.
        GALLERY_POOLS_TABLE => "CreatorUserId",  // Likely empty.
        GALLERY_TAG_ALIAS_TABLE => "CreatorUserId",  // Likely empty.
        GALLERY_TAG_IMPLICATION_TABLE => "CreatorUserId",  // Likely empty.
        FICS_STORY_TABLE => "AuthorUserId",
        FICS_CHAPTER_TABLE => "AuthorUserId",
        FICS_REVIEW_TABLE => "ReviewerUserId",  // TODO: Will these be imported?
        FICS_TAG_ALIAS_TABLE => "CreatorUserId",  // Likely empty.
        FICS_TAG_IMPLICATION_TABLE => "CreatorUserId",  // Likely empty.
        OEKAKI_POST_TABLE => "UserId",
        USER_MAILBOX_TABLE => "SenderUserId",
        USER_MAILBOX_TABLE => "RecipientUserId"
        );
    foreach ($update_mapping as $table => $field) {
        sql_query("UPDATE $table SET $field=$new_uid WHERE $field=$uid;");
    }
    // Before deleting old user, try to move some settings over (like forums signature).
    // Signature.
    if (sql_query_into($result, "SELECT Signature FROM ".FORUMS_USER_PREF_TABLE." WHERE UserId=$new_uid;", 1)) {
        $current_sig = $result->fetch_assoc()['Signature'];
        if (strlen(SanitizeHTMLTags($current_sig, "")) == 0) {
            // When tags are stripped, signature is empty.
            if (sql_query_into($result, "SELECT Signature FROM ".FORUMS_USER_PREF_TABLE." WHERE UserId=$uid;", 1)) {
                $new_sig = $result->fetch_assoc()['Signature'];
                $new_sig = SanitizeHTMLTags($new_sig, DEFAULT_ALLOWED_TAGS);
                $escaped_new_sig = sql_escape(GetSanitizedTextTruncated($new_sig, DEFAULT_ALLOWED_TAGS, MAX_FORUMS_SIGNATURE_LENGTH));
                sql_query("UPDATE ".FORUMS_USER_PREF_TABLE." SET Signature='$escaped_new_sig' WHERE UserId=$new_uid;");
            }
        }
    }
    $delete_mapping = array(
        USER_TABLE => "UserId",
        FORUMS_USER_PREF_TABLE => "UserId",  // Likely empty.
        GALLERY_USER_PREF_TABLE => "UserId",  // Likely empty.
        FICS_USER_PREF_TABLE => "UserId",  // Likely empty.
        FORUMS_UNREAD_POST_TABLE => "UserId"  // Likely empty.
        );
    foreach ($delete_mapping as $table => $field) {
        sql_query("DELETE FROM $table WHERE $field=$uid;");
    }
    sql_query("UPDATE ".GALLERY_POST_TABLE." SET UploaderId=$new_uid WHERE UploaderId=$uid;");  // Likely empty.
    sql_query("UPDATE ".GALLERY_POST_TABLE." SET FlaggerUserId=$new_uid WHERE FlaggerUserId=$uid;");  // Likely empty.
    sql_query("UPDATE ".GALLERY_TAG_TABLE." SET CreatorUserId=$new_uid WHERE CreatorUserId=$uid;");  // Likely empty.
    sql_query("UPDATE ".GALLERY_TAG_TABLE." SET ChangeTypeUserId=$new_uid WHERE ChangeTypeUserId=$uid;");  // Likely empty.
    sql_query("UPDATE ".FICS_TAG_TABLE." SET CreatorUserId=$new_uid WHERE CreatorUserId=$uid;");  // Likely empty.
    sql_query("UPDATE ".FICS_TAG_TABLE." SET ChangeTypeUserId=$new_uid WHERE ChangeTypeUserId=$uid;");  // Likely empty.

    // Now, for semantically complicated transfers.
    // For mailbox, delete messages that are sent and received by the same new user.
    sql_query("DELETE FROM ".USER_MAILBOX_TABLE." WHERE SenderUserId=$new_uid AND RecipientUserId=$new_uid");
    // Move favorites to new account, and update item stats.
    MoveFavorites(GALLERY_USER_FAVORITES_TABLE, "PostId", "UpdatePostStatistics", $uid, $new_uid);
    MoveFavorites(FICS_USER_FAVORITES_TABLE, "StoryId", "UpdateStoryStats", $uid, $new_uid);

    // For story co-authors, search and replace them manually.
    if (sql_query_into($result, "SELECT * FROM ".FICS_STORY_TABLE." WHERE INSTR(CONCAT(',', CoAuthors, ','), ',$uid,') <> 0);", 1)) {
        $stories = array();
        while ($row = $result->fetch_assoc()) {
            $stories[] = $row;
        }
        foreach ($stories as $story) {
            $sid = $story['StoryId'];
            $coauthors = explode(",", $story['CoAuthors']);
            foreach ($coauthors as &$ca) {
                if ($ca == $uid) $ca = $new_uid;
            }
            $new_coauthors = implode(",", $coauthors);
            sql_query("UPDATE ".FICS_STORY_TABLE." SET Coauthors='$new_coauthors' WHERE StoryId=$sid;");
        }
    }
    $user_uid = $user['UserId'];
    $user_username = $user['DisplayName'];
    $account_uid = $old_user['UserId'];;
    $account_username = $old_user['DisplayName'];
    LogAction("<strong><a href='/user/$user_uid/'>$user_username</a></strong> imported old account <strong><a href='/user/$account_uid/'>$account_username</a></strong>", "");
    return $ret_array;
}

function MoveFavorites($favorites_table, $item_name, $update_item_stats_fn, $old_user_id, $new_user_id) {
    $old_fav_ids = array();
    if (sql_query_into($result, "SELECT $item_name AS id FROM $favorites_table WHERE UserId=$old_user_id;", 1)) {
        while ($row = $result->fetch_assoc()) {
            $old_fav_ids[] = $row['id'];
        }
    }
    $new_fav_ids = array();
    if (sql_query_into($result, "SELECT $item_name AS id FROM $favorites_table WHERE UserId=$new_user_id;", 1)) {
        while ($row = $result->fetch_assoc()) {
            $new_fav_ids[] = $row['id'];
        }
    }
    $faves_to_move = array();
    $faves_to_delete = array();
    foreach ($old_fav_ids as $fid) {
        if (in_array($fid, $new_fav_ids)) {
            $faves_to_delete[] = $fid;
        } else {
            $faves_to_move[] = $fid;
        }
    }
    if (sizeof($faves_to_move) > 0) {
        $joined_to_move = implode(",", $faves_to_move);
        sql_query("UPDATE $favorites_table SET UserId=$new_user_id WHERE UserId=$old_user_id AND $item_name IN ($joined_to_move);");
    }
    if (sizeof($faves_to_delete) > 0) {
        $joined_to_delete = implode(",", $faves_to_delete);
        sql_query("DELETE FROM $favorites_table WHERE UserId=$old_user_id AND $item_name IN ($joined_to_delete);");
    }
    foreach ($old_fav_ids as $fid) {
        $update_item_stats_fn($fid);
    }
}

///////////////////////////////////
// Utility functions below here. //
///////////////////////////////////

// (SMF): Removes special entities from strings.  Compatibility...
function un_htmlspecialchars($string) {
	static $translation;
	if (!isset($translation))
		$translation = array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES)) + array('&#039;' => '\'', '&nbsp;' => ' ');
	return strtr($string, $translation);
}
function SMF_sha($member_name, $post_password) {
    return sha1(strtolower($member_name) . un_htmlspecialchars($post_password));
}

?>