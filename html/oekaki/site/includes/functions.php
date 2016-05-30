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
    if (isset($metadata['name']) &&
        isset($metadata['slot']) &&
        isset($metadata['width']) &&
        isset($metadata['height']) &&
        isset($metadata['imageData']) &&
        isset($metadata['elapsedSeconds']) &&
        isset($metadata['color1']) &&
        isset($metadata['color1']['r']) &&
        isset($metadata['color1']['g']) &&
        isset($metadata['color1']['b']) &&
        isset($metadata['color2']) &&
        isset($metadata['color2']['r']) &&
        isset($metadata['color2']['g']) &&
        isset($metadata['color2']['b']) &&
        isset($metadata['layers'])) {
        $layers = $metadata['layers'];
        $valid_layers = (sizeof($layers) > 0);
        foreach ($layers as $layer) {
            if (!isset($layer['name']) ||
                !isset($layer['lockOpacity']) ||
                !isset($layer['opacity']) ||
                !(isset($layer['src']) || isset($layer['data']))) {
                $valid_layers = false;
            }
        }
        if ($valid_layers) {
            return true;
        }
    }
    return false;
}

function SanitizeMetadata($metadata) {
    $result = array();
    $fields = array('name', 'slot', 'width', 'height', 'imageData', 'elapsedSeconds');
    foreach ($fields as $name) {
        $result[$name] = $metadata[$name];
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
    $user_dir_path = SITE_ROOT."user/data/oekaki/".$user['UserId']."/";
    $path = $user_dir_path."slot$slot_index/";
    $meta_path = $path.OEKAKI_METADATA_FILE_NAME;
    $empty = true;
    if (file_exists($path) && file_exists($meta_path)) {
        $metadata = json_decode(file_get_contents($meta_path), true);
        if (IsMetadataValid($metadata)) return $metadata;
    }
    return NULL;
}
?>