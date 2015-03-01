<?php
// General setup script that will be used to set up the website.
// Should include any SQL database commands that are needed.
include_once("../header.php");

// If doesn't exist, is a no-op.
sql_query("DROP TABLE User;");

do_or_die(sql_query(
    "CREATE TABLE User (
        UserId INT(11) UNSIGNED AUTO_INCREMENT,
        UserName VARCHAR(24) NOT NULL,
        DisplayName VARCHAR(24) NOT NULL,
        DisplayNameChangeTime INT(11) NOT NULL,
        Email VARCHAR(64) NOT NULL,
        Password CHAR(32) NOT NULL,
        SecretQuestion VARCHAR(256) NOT NULL,
        SecretAnswer CHAR(32) NOT NULL,
        Timezone FLOAT NOT NULL,
        Usermode INT(11) DEFAULT 1 NOT NULL,
        Permissions VARCHAR(8) NOT NULL,
        BanReason VARCHAR(256) NOT NULL,
        Title VARCHAR(64) NOT NULL,
        DOB CHAR(10) NOT NULL,
        ShowDOB TINYINT(1) DEFAULT 0 NOT NULL,
        Avatar VARCHAR(256) NOT NULL,
        JoinTime INT(11) NOT NULL,
        LastVisitTime INT(11) NOT NULL,
        KnownIPs VARCHAR(256) NOT NULL,
        PRIMARY KEY(UserId)
    );"));
    
include_once("load_sql_mock_data.php");

?>