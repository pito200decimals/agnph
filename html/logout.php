<?php
// Page for logging out of a user's account.
// URL: /logout/ => /logout.php

define("SITE_ROOT", __DIR__."/");

if (isset($_POST['submit'])) {
    include_once("includes/auth/logout.php");
    unset($user);
    unset($_COOKIE[UID_COOKIE]);
    unset($_COOKIE[SALT_COOKIE]);
}
include_once("header.php");

if (isset($user)) {
    InvalidURL();
} else {
    RenderPage("logout.tpl");
}
return;
?>