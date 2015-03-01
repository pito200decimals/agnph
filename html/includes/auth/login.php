<?php
// General code to set up a user's login cookie. Should be included INSTEAD of auth.php.

include_once(__DIR__."/../../header.php");

// Returns true and sets global $user if login successful. Also sets cookies.
// Returns false and unsets global $user if login unsuccessful. Also unsets cookies.
// Either way, cookies are set/unset in some way.
function Login($username, $password) {
    // Mocked data.
    $username = "User 1";
    $password = "Password 1";
    
    // TODO: Look up only uid, email and password from user db (enough to compute the cookies).
    $escapedName = sql_escape($username);
    $result = sql_query("SELECT UserID,Email,Password FROM ".USER_TABLE." WHERE UserName='$escapedName' LIMIT 1;");
    if (!$result || $result->num_rows <= 0) {
        return false;
    }
    $user = $result->fetch_assoc();
    $uid = $user['UserID'];
    $email = $user['Email'];
    $encryptedPassword = $user['Password'];
    
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

if (!isset($user)) {
    // auth.php did not find a user logged in. We can safely perform the login now.
    Login($_POST['username'], $_POST['password']);
}

?>