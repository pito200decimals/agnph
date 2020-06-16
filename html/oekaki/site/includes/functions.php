<?php
// General functions for the oekaki section.

include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."includes/util/core.php");
include_once(SITE_ROOT."includes/util/user.php");

function CanUserCreatePost($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['OekakiPermissions'] == 'A') return true;
    // Restrict user based on permissions.
    if ($user['OekakiPermissions'] == 'R') return false;
    return true;
}

function CanUserCreateComment($user) {
    if (!IsUserActivated($user)) return false;
    if ($user['OekakiPermissions'] == 'A') return true;
    // Restrict user based on permissions.
    if ($user['OekakiPermissions'] == 'R') return false;
    return true;
}

function CanUserDeletePost($user, $post) {
    if (!IsUserActivated($user)) return false;
    if ($user['OekakiPermissions'] == 'A') return true;
    // Restrict user based on permissions. Restricted users cannot delete their own comments/posts.
    if ($user['OekakiPermissions'] == 'R') return false;
    if ($user['UserId'] == $post['UserId']) return true;  // Creator can delete.
    return false;
}


function IsMetadataValid($metadata) {
    if (!isset($metadata['name'])) return false;
    if (!isset($metadata['slot'])) return false;
    if (!isset($metadata['width'])) return false;
    if (!isset($metadata['height'])) return false;
    if (!isset($metadata['imageData'])) return false;
    if (!isset($metadata['elapsedSeconds'])) return false;
    if (!isset($metadata['color1'])) return false;
    if (!isset($metadata['color1']['r'])) return false;
    if (!isset($metadata['color1']['g'])) return false;
    if (!isset($metadata['color1']['b'])) return false;
    if (!isset($metadata['color2'])) return false;
    if (!isset($metadata['color2']['r'])) return false;
    if (!isset($metadata['color2']['g'])) return false;
    if (!isset($metadata['color2']['b'])) return false;
    if (isset($metadata['version'])) {
        if (strcmp($metadata['version'], "1.00.00") >= 0) {
            if (!isset($metadata['toolStates'])) return false;
            foreach ($metadata['toolStates'] as $state) {
                if (!isset($state['name'])) return false;
                if (!isset($state['state'])) return false;
            }
            if (isset($metadata['hasReplay']) && $metadata['hasReplay']) {
                if (!isset($metadata['replayData'])) return false;
                foreach ($metadata['replayData'] as $actionItem) {
                    if (!isset($actionItem['id'])) return false;
                }
            }
        }
    }
    if (!isset($metadata['layers'])) return false;
    if ($metadata['width'] > MAX_OEKAKI_IMAGE_SIZE || $metadata['height'] > MAX_OEKAKI_IMAGE_SIZE) return false;
    $layers = $metadata['layers'];
    if (!(0 < sizeof($layers) && sizeof($layers) <= MAX_OEKAKI_NUM_LAYERS)) return false;
    foreach ($layers as $layer) {
        if (!isset($layer['name'])) return false;
        if (!isset($layer['lockOpacity'])) return false;
        if (!isset($layer['opacity'])) return false;
        if (!(isset($layer['src']) || isset($layer['data']))) return false;
    }
    return true;
}

function SanitizeMetadata($metadata) {
    // TODO: Properly analyze version, toolStates, hasReplay, replayData.
    $result = array();
    $fields = array(
        'name' => "",
        'version' => "0.00.00",
        'slot' => -1,
        'width' => 800,
        'height' => 600,
        'imageData' => "",
        'elapsedSeconds' => 0,
        'selectedLayer' => 0,
        'toolStates' => array(),
        'hasReplay' => false,
        'replayData' => array());
    foreach ($fields as $name => $value) {
        if (isset($metadata[$name])) {
            $result[$name] = $metadata[$name];
        } else {
            $result[$name] = $value;
        }
    }
    $result['color1'] = array(
        'r' => $metadata['color1']['r'],
        'g' => $metadata['color1']['g'],
        'b' => $metadata['color1']['b']);
    $result['color2'] = array(
        'r' => $metadata['color2']['r'],
        'g' => $metadata['color2']['g'],
        'b' => $metadata['color2']['b']);
    $result['layers'] = array();
    foreach ($metadata['layers'] as &$layer) {
        $temp_layer = array(
            'name' => $layer['name'],
            'lockOpacity' => $layer['lockOpacity'],
            'opacity' => $layer['opacity']);
        if (isset($layer['src'])) $temp_layer['src'] = $layer['src'];
        if (isset($layer['data'])) $temp_layer['data'] = $layer['data'];
        $result['layers'][] = $temp_layer;
    }
    return $result;
}

function GetValidMetadataOrNull($slot_index) {
    global $user;
    $uid = $user['UserId'];
    $user_dir_path = SITE_ROOT."user/data/oekaki/$uid/";
    $path = $user_dir_path."slot$slot_index/";
    $meta_path = $path.OEKAKI_METADATA_FILE_NAME;
    $empty = true;
    if (file_exists($path) && file_exists($meta_path)) {
        $metadata = json_decode(file_get_contents($meta_path), true);
        if (IsMetadataValid($metadata)) return $metadata;
    }
    return NULL;
}

function GetActiveLivestreams() {
    if (!sql_query_into($result, "SELECT T.UserId, DisplayName, Timestamp FROM ".OEKAKI_LIVESTREAM_TABLE." T INNER JOIN ".USER_TABLE." U ON T.UserId=U.UserId WHERE 1;")) return array();
    $streams = array();
    $now = time();
    while ($row = $result->fetch_assoc()) {
        $streams[] = array(
            "UserId" => $row['UserId'],
            "DisplayName" => $row['DisplayName'],
            "Timestamp" => $row['Timestamp'],
            "Duration" => FormatDuration($now - $row['Timestamp']),
        );
    }
    return $streams;
}
?>