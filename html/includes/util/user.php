<?php
// Utility functions for user permissions.

function IsUserBanned($user) {
    return $user['Usermode'] == -1;
}
function IsUserUnactivated($user) {
    return $user['Usermode'] == 0;
}
function IsUserActivated($user) {
    return $user['Usermode'] == 1;
}
?>