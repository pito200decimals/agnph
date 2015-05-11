<?php
// Page for logging out of a user's account.
// URL: /logout/ => /logout.php

include_once("includes/auth/logout.php");
include_once("header.php");

RenderPage("logout.tpl");
return;
?>