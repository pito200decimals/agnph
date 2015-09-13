<?php
// General user-authentication code. Will be included and run once at the beginning of each public-facing page.

include_once(__DIR__."/../../header.php");

if (isset($user)) {
    debug_die("Already defined \$user: $user");
}

// Returns true and sets the global $user on successful authentication.
// Returns false and unsets the global $user on unsuccessful authentication.
// If false is returned, cookies are automatically unset.
function AuthenticateUser($uid, $salt) {
    global $user, $user_banned, $user_ban_timestamp, $user_ban_reason;
    debug("Authenticating user with uid=$uid, salt=$salt");
    // TODO: Load only main user table?
    LoadAllUserPreferences($uid, $user, true);
    $uid = $user['UserId'];
    if ($user['Usermode'] == -1) {
        // Account has been banned. Disallow login if it has not expired.
        $ban_expiration = $user['BanExpireTime'];
        if ($ban_expiration != -1 && time() > $ban_expiration) {
            // Unban account.
            debug("User account has been un-banned");
            sql_query("UPDATE ".USER_TABLE." SET Usermode=1 WHERE UserId=$uid;");
        } else {
            debug("User account has been banned");
            UnsetCookies();
            $user_banned = true;
            $user_ban_timestamp = $user['BanExpireTime'];
            $user_ban_reason = $user['BanReason'];
            $user = null;
            unset($user);
            return false;
        }
    }
    
    $targetSalt = md5($user['Email'].$user['Password']);
    if ($targetSalt !== $salt) {
        // Cookie did not match user credentials, do not log in.
        debug("User did not pass authentication");
        UnsetCookies();
        $user = null;
        unset($user);
        return false;
    }
    if (!CanLogin($user)) {  // Disable logins when in site maintenance mode.
        $user = null;
        unset($user);
        return false;
    }
    debug("User has been authenticated!");
    debug($user);
    return true;
}

// Try to authenticate if cookies exist.
if (CookiesExist()) {
    AuthenticateUser($_COOKIE[UID_COOKIE], $_COOKIE[SALT_COOKIE]);
} else {
    debug("User is a guest!");
    // Normal guest user. Don't define $user or cookies.
    unset($user);
    return;
}

?>