<?php
// General script to load mock data into the SQL tables for testing purposes.

include_once("../header.php");

do_or_die(sql_query(
    "INSERT INTO ".USER_TABLE."
    (UserID, UserName, DisplayName, Email, Password)
    VALUES
    (1, 'User 1', 'User 1', 'Email 1', '".md5("Password 1")."');"));
do_or_die(sql_query(
    "INSERT INTO ".USER_TABLE."
    (UserID, UserName,  DisplayName, Email, Password)
    VALUES
    (2, 'User 2', 'User 2', 'Email 2', '".md5("Password 2")."');"));
do_or_die(sql_query(
    "INSERT INTO ".USER_TABLE."
    (UserID, UserName,  DisplayName, Email, Password)
    VALUES
    (3, 'User 3', 'User 3', 'Email 3', '".md5("Password 3")."');"));

?>