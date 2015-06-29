<?php
// Basic functions for account registration.

function HashAuthKey($username, $email, $joinTime) {
    return md5(md5($username).$joinTime.md5($email));
}
?>