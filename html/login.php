<?php
// Page for logging in to a user's account.
// URL: /login/ => /login.php

define("SITE_ROOT",  __DIR__."/");
define("DEBUG", true);

// TODO: Remove after site testing is complete.
if (isset($_GET['debug']) && $_GET['debug'] == true) {
    $_POST['username'] = "User1";
    $_POST['password'] = "Password 1";
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    include_once(SITE_ROOT."includes/auth/login.php");
    if (isset($user)) {
        header("Location: /");
        return;
    } else {
        $vars['error'] = "Invalid username/password";
        $vars['username'] = $_POST['username'];
    }
} else {
    include_once("header.php");
}

if (isset($user)) {
    header("Location: /");
    return;
}


// Pages will set their content here.
$content = "";

// This is how to output the template.
$vars['content'] = $content;
RenderPage("login.tpl");
return;
?>