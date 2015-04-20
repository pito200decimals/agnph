<?php
// Core utility functions for general sql code.

function sql_query($sql) {
    global $sqlconn;
    debug("<strong>[SQL]:</strong> $sql");
    return $sqlconn->query($sql);
}

// Puts the result of the query into the $result variable.
// Returns true if the query is successful and the number of rows is at least $min_rows, false otherwise.
function sql_query_into(&$result, $query, $min_rows = 1) {
    $result = sql_query($query);
    if ($result && $result->num_rows >= $min_rows) {
        return true;
    } else {
        $result = false;
        return false;
    }
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
    debug_die("Connection failed: " . $sqlconn->connect_error);
}
if (!$sqlconn->set_charset("utf8")) {
    debug_die("Connection failed: " . $sqlconn->error);
} else {
    debug("Current character set: ".$sqlconn->character_set_name());
}

?>