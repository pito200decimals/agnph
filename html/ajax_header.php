<?php
// Standard header for all AJAX. Just includes common headers.
// Assumes SITE_ROOT is defined.

// Include common headers.
include_once(SITE_ROOT."includes/config.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/sql.php");
include_once(SITE_ROOT."includes/util/logging.php");
include_once(SITE_ROOT."includes/util/user.php");
include_once(SITE_ROOT."includes/util/logging.php");
// Authenticate logged-in user.
include_once(SITE_ROOT."includes/auth/auth.php");

header('Content-type: application/json; charset=utf-8');

?>