<?php
// Register a new account page, after email authentication.
// URL: /register/success/
// URL: /user/register/register_success.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/file.php");
include_once(SITE_ROOT."includes/util/logging.php");
include_once(SITE_ROOT."includes/auth/email_auth.php");
include_once(SITE_ROOT."user/register/send_register_email_functions.php");

if (!CheckSuperAdminPerms()) {
    RenderErrorPage("Invalid permissions");
}

if (!isset($_GET['uid']) || !is_numeric($_GET['uid'])) {
    RenderErrorPage("Invalid URL");
}
$uid = (int)$_GET['uid'];

sql_query_into($result, "SELECT * FROM ".USER_TABLE." WHERE UserId=$uid AND Usermode=0;", 1) or RenderErrorPage("Partially-registered user not found");
$row = $result->fetch_assoc();
$username = $row['UserName'];
$email = $row['Email'];

$escaped_email = sql_escape($email);
sql_query("DELETE FROM ".SECURITY_EMAIL_TABLE." WHERE Email='$escaped_email';");  // Delete existing auth code entry.

SendValidationEmailLink($uid, $username, $email, time()) or RenderErrorPage("Email failed to send");

// Show error page format with success message.
RenderErrorPage("Email resent");
?>