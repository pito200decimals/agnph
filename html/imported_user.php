<?php
// Site home page.

// Site includes, including login authentication.
include_once("header.php");

if (!isset($user)) {
    Redirect("/login/");
}
$vars['user'] = $user;

RenderPage("imported_user.tpl");
return;
?>