<?php
// Register a new account page.
// URL: /register/
// URL: /user/register/register_input.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/html_funcs.php");
include_once(SITE_ROOT."includes/util/date.php");
include_once(SITE_ROOT."includes/auth/email_auth.php");
include_once(SITE_ROOT."user/register/send_register_email_functions.php");
include_once(SITE_ROOT."user/includes/functions.php");

if (IsMaintenanceMode()) PostBanner("Site is in read-only mode, account registration has been disabled", "red", false);

if (isset($user)) {
    Redirect("/");
}
if (isset($_POST['username']) &&
    isset($_POST['email']) &&
    isset($_POST['email-confirm']) &&
    isset($_POST['password']) &&
    isset($_POST['password-confirm']) &&
    isset($_POST['bday']) &&
    isset($_POST['captcha'])) {
    if (IsMaintenanceMode()) MaintenanceError();
    $success = true;
    // Check username format.
    $username = mb_strtolower($_POST['username'], "UTF-8");
    if (strlen($username) < MIN_USERNAME_LENGTH) {
        ShowErrorBanner("Username too short");
        $success = false;
    } else if (strlen($username) > MAX_USERNAME_LENGTH) {
        ShowErrorBanner("Username too long");
        $success = false;
    } else if (!mb_ereg("^[a-z0-9_]+$", $username)) {  // Check this first for this error message.
        ShowErrorBanner("Username must consist of letters, numbers or _");
        $success = false;
    } else if (!mb_ereg("^[a-z][a-z0-9_]+$", $username)) {  // Must start with A-Z.
        ShowErrorBanner("Username must start with a letter");
        $success = false;
    } else if (!IsValidUsername($username)) {
        ShowErrorBanner("Invalid username");
        $success = false;
    }
    // Check email.
    $email = $_POST['email'];
    if ($email != $_POST['email-confirm']) {
        ShowErrorBanner("Mismatched email addresses");
        $success = false;
    }
    if (!ValidateEmail($email)) {
        ShowErrorBanner("Invalid email address");
        $success = false;
    }
    // Check matching password.
    $password = $_POST['password'];
    if ($password !== $_POST['password-confirm']) {
        ShowErrorBanner("Mismatched passwords");
        $success = false;
    }
    // Check parsable birthday.
    $bday = ParseDate($_POST['bday']);
    if ($bday == null) {
        ShowErrorBanner("Invalid birthday");
        $success = false;
    }
    // Check for captcha validation.
    $captcha = $_POST['captcha'];
    if (!isset($_SESSION['captcha_code'])) {
        ShowErrorBanner("Form expired, please try again");
        $success = false;
    } else if ($captcha !== $_SESSION['captcha_code']) {
        ShowErrorBanner("Captcha did not match");
        $success = false;
    }

    // Do database checks for Username/DisplayName/Email.
    $escaped_username = sql_escape($username);
    // Fail if username taken, or display name taken with an activated account.
    // This will allow duplicate display names when including unactivated accounts, but presumably people will want to link this account.
    if (sql_query_into($result,
        "SELECT * FROM ".USER_TABLE." WHERE
        UPPER(UserName)=UPPER('$escaped_username') OR (
            UPPER(DisplayName)=UPPER('$escaped_username') AND
            ".ACCOUNT_NOT_IMPORTED_SQL_CONDITION.")
        LIMIT 1;", 1)) {
        // Exists a duplicate username.
        ShowErrorBanner("Username already taken.");
        $success = false;
    }
    $escaped_email = sql_escape($email);
    // Fail if Email taken with an activated account. Also helps in preventing spam.
    if (sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UPPER(Email)=UPPER('$escaped_email') AND ".ACCOUNT_NOT_IMPORTED_SQL_CONDITION." LIMIT 1;", 1)) {
        // Exists a duplicate email on a non-imported account.
        ShowErrorBanner("Email address already in use.");
        $success = false;
    }

    if ($success) {
        if (HandlePostSuccess($username, $email, $password, $bday)) {
            $_SESSION['register_email'] = $email;
            unset($_SESSION['captcha_code']);
            Redirect("/register/confirm/");
            return;
        }
    }
    $vars['username'] = $username;
    $vars['email'] = $email;
    $vars['bday'] = $_POST['bday'];
} else {
    $vars['bday'] = "mm/dd/yyyy";
}
$year = date("Y");
$vars['max_bday'] = ($year - 5)."-01-01";
$vars['min_bday'] = "1900-01-01";

$vars['registerDisclaimerMessage'] = GetSiteSetting(REGISTER_DISCLAIMER_KEY, "");

// This is how to output the template.
RenderPage("user/register.tpl");
return;

// Delayed session banner due to redirect.
function ShowErrorBanner($msg) {
    PostSessionBanner($msg, "red");
}

function HandlePostSuccess($username, $email, $password, $bday) {
    // Create user table values.
    $escaped_username = sql_escape(GetSanitizedTextTruncated($username, NO_HTML_TAGS, MAX_USERNAME_LENGTH));
    $escaped_email = sql_escape(GetSanitizedTextTruncated($email, NO_HTML_TAGS, MAX_USER_EMAIL_LENGTH));
    $hashed_password = md5($password);
    $escaped_password = sql_escape($hashed_password);  // Okay to not sanitize this.
    $escaped_bday = sql_escape($bday);  // Not necessary, but just in case...
    $register_time = time();
    $escaped_ip = sql_escape($_SERVER['REMOTE_ADDR']);
    $success = sql_query("INSERT INTO ".USER_TABLE."
        (UserName, DisplayName, Email, Password, DOB, JoinTime, RegisterIP)
        VALUES
        ('$escaped_username', '$escaped_username', '$escaped_email', '$escaped_password', '$escaped_bday', $register_time, '$escaped_ip');");
    if ($success) {
        $uid = sql_last_id();
        $interval = REGISTER_ACCOUNT_SQL_EVENT_DURATION;
        $success = sql_query("
            CREATE EVENT delete_expired_register_account_$uid
            ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL $interval DO
            DELETE FROM ".USER_TABLE." WHERE UserId=$uid AND Usermode=0;");
    }

    global $user;
    $user = array("UserId" => $uid);  // Set mock user so that logging can occur.

    if ($success) {
        $success = SendValidationEmailLink($uid, $username, $email, $register_time);
    }
    if ($success) {
        $ip = $_SERVER['REMOTE_ADDR'];
        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> registered from IP address $ip", "");
    } else {
        ShowErrorBanner("Error creating account, please try again later");
        LogAction("<strong><a href='/user/$uid/'>$username</a></strong> failed to register from IP address $ip", "");
    }
    unset($user);  // Keep user unset outside of this function.
    return $success;
}
?>