<?php
// General code to set up a user's login cookie. Should be included INSTEAD of auth.php.

include_once(__DIR__."/../../header.php");

if (!isset($user)) {
    // auth.php did not find a user logged in. We can safely perform the login now.
    Login(mb_strtolower($_POST['username'], "UTF-8"), $_POST['password']);
}

// Returns true and sets global $user if login successful. Also sets cookies.
// Returns false and unsets global $user if login unsuccessful. Also unsets cookies.
// Either way, cookies are set/unset in some way.
function Login($username, $password) {
    if (LoginNormal($username, $password)) return true;
    else if (LoginImported($username, $password)) return true;
    return false;
}

function LoginNormal($username, $password) {
    $escapedName = sql_escape($username);
    if (!sql_query_into($result, "SELECT UserId,UserName,Email,Password FROM ".USER_TABLE." WHERE (UPPER(UserName)=UPPER('$escapedName') OR UPPER(Email)=UPPER('$escapedName')) AND Usermode<>0 AND RegisterIP<>'' LIMIT 1;", 1)) {
        return false;
    }
    $usr = $result->fetch_assoc();
    $uid = $usr['UserId'];
    $email = $usr['Email'];
    $encryptedPassword = $usr['Password'];
    
    if ($encryptedPassword !== md5($password)) {
        // Did not provide correct password.
        debug("Incorrect credentials provided");
        UnsetCookies();
        return false;
    }
    $salt = md5($email.$encryptedPassword);
    if (AuthenticateUser($uid, $salt)) {
        setcookie(UID_COOKIE, $uid, time() + COOKIE_DURATION, "/");
        setcookie(SALT_COOKIE, $salt, time() + COOKIE_DURATION, "/");
        return true;
    } else {
        // Cookies are unset by AuthenticateUser().
        return false;
    }
}

function LoginImported($username, $password) {
    global $newly_imported;
    $newly_imported = false;
    $escapedName = sql_escape(IMPORTED_ACCOUNT_USERNAME_PREFIX.$username);
    $escapedEmail = sql_escape($username);
    if (!sql_query_into($result,
        "SELECT UserId,UserName,Email,ImportForumsPassword,ImportGalleryPassword,ImportFicsPassword,ImportOekakiPassword
        FROM ".USER_TABLE."
        WHERE (UPPER(UserName)=UPPER('$escapedName') OR UPPER(Email)=UPPER('$escapedEmail')) AND Usermode<>0 AND RegisterIP='';", 1)) {
        return false;
    }
    $fn_field_map = array(
        array("SMF_sha", "ImportForumsPassword"),
        array(function($u, $p) { return sha1(GALLERY_CRYPT_SALT."--$p--"); }, "ImportGalleryPassword"),
        array(function ($u, $p) { return md5($p); }, "ImportFicsPassword"),
        array(function($u, $p) { return crypt($p, OEKAKI_CRYPT_SALT); }, "ImportOekakiPassword"));
    while ($row = $result->fetch_assoc()) {
        $db_username = $row['UserName'];
        if (!startsWith($db_username, IMPORTED_ACCOUNT_USERNAME_PREFIX)) continue;
        $db_username = mb_substr($db_username, mb_strlen(IMPORTED_ACCOUNT_USERNAME_PREFIX));
        // Try to login with each imported password field.
        foreach ($fn_field_map as $pair) {
            $hash = $pair[0]($db_username, $password);
            $db_hash = $row[$pair[1]];
            if ($hash == $db_hash) {
                // Update password.
                $encryptedPassword = md5($password);
                $email = $row['Email'];
                $uid = $row['UserId'];
                $ip = $_SERVER['REMOTE_ADDR'];
                sql_query("UPDATE ".USER_TABLE." SET Username='$db_username',Password='$encryptedPassword',RegisterIP='$ip' WHERE UserId=$uid;");
                // Also send intro PM.
                $now = time();
                $title = sql_escape("Welcome back to AGNPH");
                $message_content = GetSiteSetting(IMPORT_USER_WELCOME_PM_KEY, "");
                if (mb_strlen($message_content)) {
                    $message_content = sql_escape(GetSanitizedTextTruncated($message_content, DEFAULT_ALLOWED_TAGS, MAX_PM_LENGTH));
                    sql_query("INSERT INTO ".USER_MAILBOX_TABLE." (SenderUserId, RecipientUserId, ParentMessageId, Timestamp, Title, Content, MessageType)
                        VALUES (-1, $uid, -1, $now, '$title', '$content', 1);");
                }
                // Log action.
                $username = $db_username;
                global $user;
                $user = array("UserId" => $uid);
                LogAction("<strong><a href='/user/$uid/'>$username</a></strong> logged in and imported their old account.", "");
                unset($user);
                
                // Actually log user in.
                $salt = md5($email.$encryptedPassword);
                if (AuthenticateUser($uid, $salt)) {
                    setcookie(UID_COOKIE, $uid, time() + COOKIE_DURATION, "/");
                    setcookie(SALT_COOKIE, $salt, time() + COOKIE_DURATION, "/");
                    $newly_imported = true;
                    return true;
                } else {
                    return false;
                }
            } else {
                continue;  // Continue to next user entry.
            }
        }
    }
    return false;
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