<?php
// Page for logging out of a user's account.
// URL: /logout/ => /logout.php

include_once("includes/auth/logout.php");
unset($user);
unset($_COOKIE[UID_COOKIE]);
unset($_COOKIE[SALT_COOKIE]);
include_once("header.php");

RenderPage("logout.tpl");
return;
?>