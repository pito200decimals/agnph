<?php
// Receives IRC update POST's and echos them back.

define("SITE_ROOT", "./");
include_once("ajax_header.php");

define("IRC_LOG_FILE", "irc.log");

function BadRequest($code = 400) {
    http_response_code($code);
    exit();
}

// Checks message file data format, and restructures it into json-ready form.
function ParseMessages($msgs) {
    $result = array();
    for ($i = 0; $i < sizeof($msgs); ) {
        if ($i >= sizeof($msgs)) AJAXErr();
        $time = $msgs[$i++];
        if ($i >= sizeof($msgs)) AJAXErr();
        $nick = $msgs[$i++];
        if ($i >= sizeof($msgs)) AJAXErr();
        $type = $msgs[$i++];
        $timestamp = FormatDate($time, IRC_MIRROR_TIME_FORMAT);
        switch ($type) {
            case "privmsg":
                if ($i >= sizeof($msgs)) AJAXErr();
                // $result[] = "[$timestamp] <$nick> ".$msgs[$i++];
                $result[] = array("time" => $timestamp, "nick" => $nick, "type" => "msg", "text" => $msgs[$i++]);
                break;
            case "action":
                if ($i >= sizeof($msgs)) AJAXErr();
                // $result[] = "[$timestamp] <$nick> ".$msgs[$i++];
                $result[] = array("time" => $timestamp, "nick" => $nick, "type" => "action", "text" => $msgs[$i++]);
                break;
            case "nick":
                if ($i >= sizeof($msgs)) AJAXErr();
                // $result[] = "[$timestamp] $nick is now known as ".$msgs[$i++];
                $result[] = array("time" => $timestamp, "nick" => $nick, "type" => "nick", "new-nick" => $msgs[$i++]);
                break;
            case "join":
                // $result[] = "[$timestamp] $nick has joined #agnph";
                $result[] = array("time" => $timestamp, "nick" => $nick, "type" => "join");
                break;
            case "part":
                // $result[] = "[$timestamp] $nick has left #agnph";
                $result[] = array("time" => $timestamp, "nick" => $nick, "type" => "part");
                break;
            case "quit":
                // $result[] = "[$timestamp] $nick has quit";
                $result[] = array("time" => $timestamp, "nick" => $nick, "type" => "quit");
                break;
            default:
                AJAXErr();
                break;
        }
    }
    return $result;
}

if (isset($_POST['active-users']) && isset($_POST['msgs']) && isset($_POST['secret-key']) && is_numeric($_POST['active-users']) && $_POST['secret-key'] == IRC_MIRROR_POST_SECRET_KEY) {
    // Post new log.
    $num_users = (int)$_POST['active-users'];
    if ($num_users < 0) $num_users = 0;

    $msgs = explode("\n", $_POST['msgs']);

    ParseMessages($msgs);
    $data = array($num_users, $msgs);
    
    $fp = fopen(SITE_ROOT.IRC_LOG_FILE, "c");
    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        fwrite($fp, serialize($data));
        fflush($fp);
        flock($fp, LOCK_UN);
    } else {
        // Couldn't get file lock, just post log next time.
    }
    echo json_encode(array("success" => true));
    exit();
}

// Fetch irc log.
if (!isset($user)) AJAXErr();  // Don't allow view when not logged in.
if (!file_exists(SITE_ROOT.IRC_LOG_FILE)) exit();
$fp = fopen(SITE_ROOT.IRC_LOG_FILE, "r");
if (flock($fp, LOCK_SH)) {
    $data = fread($fp, filesize(SITE_ROOT.IRC_LOG_FILE));
    flock($fp, LOCK_UN);
    $data = unserialize($data);
    if (sizeof($data) != 2) AJAXErr();
    $num_users = $data[0];
    $msgs = ParseMessages($data[1]);
    echo json_encode(array("active" => $num_users, "log" => $msgs));
    exit();
} else {
    // Couldn't get file lock.
    AJAXErr();
}
?>