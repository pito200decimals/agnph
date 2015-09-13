<?php
// Page for handling account linking.
// URL: /user/account/link/ => account_link.php

include_once("../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/table_data.php");
include_once(SITE_ROOT."user/includes/functions.php");

if (!isset($user)) {
    RenderErrorPage("Must be logged in to access this page");
}

if (isset($_POST['service'])) {
    HandlePost();
    // If this returned, we have an error.
    err("Unexpected error, please try again");
}

// This is how to output the template.
RenderPage("user/account_link.tpl");
return;

function err($msg) {
    PostSessionBanner($msg, "red");
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}

function success($msg) {
    PostSessionBanner($msg, "green");
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}

function HandlePost() {
    if (!CanPerformSitePost()) MaintenanceError();
    $service = $_POST['service'];
    switch ($service) {
        case "forums":
            ProcessPost("SMF_sha", "forums", "ImportForumsPassword");
            break;
        case "gallery":
            // HandleGalleryPost();
            break;
        case "fics":
            ProcessPost(function ($u, $p) { return md5($p); }, "fics", "ImportFicsPassword");
            break;
        case "oekaki":
            ProcessPost(function($u, $p) { return crypt($p, OEKAKI_CRYPT_SALT); }, "oekaki", "ImportOekakiPassword");
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
        if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UPPER(Username)=UPPER('$escaped_username') AND RegisterIP='' LIMIT 1;", 1)) {
            $old_user = $result->fetch_assoc();
            $expected_hashed_password = $old_user[$field];
            if ($hashed_password == $expected_hashed_password) {
                MigrateAccount($old_user['UserId']);
                success("Account linked successfully");
            } else {
                err("Invalid password");
            }
        } else {
            err(ucfirst($section)." account not found");
        }
    } else {
        // Invalid parameters, return and raise error later.
    }
}

function MigrateAccount($uid) {
    global $user;
    $new_uid = $user['UserId'];
    LoadSingleTableEntry(array(USER_TABLE), "UserId", $uid, $old_user);

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
        FICS_TAG_IMPLICATION_TABLE => "CreatorUserId"  // Likely empty.
        );
    foreach ($update_mapping as $table => $field) {
        sql_query("UPDATE $table SET $field=$new_uid WHERE $field=$uid;");
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
    // For mailbox, we shouldn't be able to send any messages to these accounts. Just delete any messages we find.
    sql_query("DELETE FROM ".USER_MAILBOX_TABLE." WHERE SenderUserId=$uid;");
    sql_query("DELETE FROM ".USER_MAILBOX_TABLE." WHERE RecipientUserId=$uid;");
    // For favorites, these should not exist for imported accounts (Normally, statistics would need to be updated).
    sql_query("DELETE FROM ".GALLERY_USER_FAVORITES_TABLE." WHERE UserId=$uid;");
    sql_query("DELETE FROM ".FICS_USER_FAVORITES_TABLE." WHERE UserId=$uid;");
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
    if (isset($old_user)) {
        LogAction("Migrated acrhive account $uid(".$old_user['DisplayName'].") to user account $new_uid(".$user['DisplayName'].")");
    } else {
        LogAction("Migrated acrhive account $uid(?ERR?) to user account $new_uid(".$user['DisplayName'].")");
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