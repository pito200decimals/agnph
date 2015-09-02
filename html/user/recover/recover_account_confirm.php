<?php
// Confirmation Page for recovering a user account
// URL: /recover/confirm/ => /recover_account_confirm.php

include_once("../../header.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");

if (isset($user)) {
    header("Location: /");
    exit();
}

if (!isset($_SESSION['recovery_email'])) {
    header("Location: /recover/");
    exit();
}
$vars['email'] = $_SESSION['recovery_email'];
RenderPage("user/recover_confirm.tpl");
return;

?>