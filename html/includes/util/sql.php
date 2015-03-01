<?php
// Core utility functions for general sql code.

function sql_query($sql) {
    global $sqlconn;
    debug("SQL: $sql");
    return $sqlconn->query($sql);
}

function sql_last_id() {
    global $sqlconn;
    return $sqlconn->insert_id;
}

function sql_escape($string) {
    global $sqlconn;
    return $sqlconn->real_escape_string($string);
}

// Create connection
$sqlconn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if ($sqlconn->connect_error || mysqli_connect_error()) {
    debug_die("Connection failed: " . $conn->connect_error);
}

?>