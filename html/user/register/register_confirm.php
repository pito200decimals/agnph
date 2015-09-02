<?php
// Register a new account page, after email authentication.
// URL: /register/confirm/
// URL: /user/register/register_success.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/file.php");

if (isset($user)) {
    header("Location: /");
    exit();
}

if (!isset($_SESSION['register_email'])) {
    header("Location: /register/");
    exit();
}
$vars['email'] = $_SESSION['register_email'];
// This is how to output the template.
RenderPage("user/register_confirm.tpl");
return;
?>