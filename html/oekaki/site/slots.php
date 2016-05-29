<?php

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");

define("METADATA_FILE_NAME", "data.txt");

if (!isset($user)) {
    AJAXErr();
}
$user_dir_path = SITE_ROOT."user/data/oekaki/".$user['UserId']."/";

if (isset($_GET['list'])) {
    ListSlots();
} else if (isset($_GET['metadata'])) {
    $slot = $_GET['metadata'];
    if (!is_numeric($slot)) {
        AJAXErr();
    } else if ($slot < 0 || $slot >= MAX_OEKAKI_SAVE_SLOTS) {
        AJAXErr();
    }
    GetMetadata($slot);
} else if (isset($_GET['save'])) {
    $slot = $_GET['save'];
    if (!is_numeric($slot)) {
        AJAXErr();
    } else if ($slot < 0 || $slot >= MAX_OEKAKI_SAVE_SLOTS) {
        AJAXErr();
    }
    $data = file_get_contents('php://input');
    $data = json_decode($data, true);
    if (json_last_error() != JSON_ERROR_NONE) {
        AJAXErr();
    }
    SaveImage($slot, $data);
}
// Else, AJAX error.
AJAXErr();

function IsMetadataValid($metadata) {
    if (isset($metadata['name']) &&
        isset($metadata['slot']) &&
        isset($metadata['width']) &&
        isset($metadata['height']) &&
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
    $fields = array('name', 'slot', 'width', 'height', 'elapsedSeconds');
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
    global $user_dir_path;
    $path = $user_dir_path."slot$slot_index/";
    $meta_path = $path.METADATA_FILE_NAME;
    $empty = true;
    if (file_exists($path) && file_exists($meta_path)) {
        $metadata = json_decode(file_get_contents($meta_path), true);
        if (IsMetadataValid($metadata)) return $metadata;
    }
    return NULL;
}

function ListSlots() {
    global $user;
    global $user_dir_path;
    $result = array();
    for ($i = 0; $i < MAX_OEKAKI_SAVE_SLOTS; $i++) {
        $metadata = GetValidMetadataOrNull($i);
        if ($metadata == NULL) {
            $slot = array(
                "index" => $i,
                "empty" => true,
                "name" => "Empty",
                "metadata" => "",
                );
        } else {
            $slot = array(
                "index" => $i,
                "empty" => false,
                "name" => $metadata['name'],
                "metadata" => "/oekaki/slots/$i/",
                );
        }
        array_push($result, $slot);
    }
    echo json_encode($result);
    exit();
}

function GetMetadata($slot_index) {
    global $user;
    global $user_dir_path;
    $metadata = GetValidMetadataOrNull($slot_index);
    if ($metadata == NULL) {
        AJAXErr();
    } else {
        echo json_encode($metadata);
        exit();
    }
}

function SaveImage($slot_index, $metadata) {
    global $user;
    global $user_dir_path;
    $path = $user_dir_path."slot$slot_index/";
    $meta_path = $path.METADATA_FILE_NAME;
    function check_base64_image($base64) {
        $img = imagecreatefromstring(base64_decode($base64));
        if (!$img) return false;
        $tmp_path = 'tmp.png';
        imagepng($img, $tmp_path);
        $info = getimagesize($tmp_path);
        unlink($tmp_path);
        if ($info[0] > 0 && $info[1] > 0 && $info['mime']) return true;
        return false;
    }

    if (!IsMetadataValid($metadata)) {
        AJAXErr();
    }
    // Check for image data.
    $expected_base64_prefix = "data:image/png;base64,";
    foreach ($metadata['layers'] as $layer) {
        if (!isset($layer['data'])) AJAXErr();
        if (!startsWith($layer['data'], $expected_base64_prefix)) AJAXErr();
        $base64_data = substr($layer['data'], strlen($expected_base64_prefix));
        if (!check_base64_image($base64_data)) {
            AJAXErr();
        }
    }
    $metadata = SanitizeMetadata($metadata);
    if (!file_exists($path)) {
        mkdir($path, 0777, true /* recursive */);
    }
    if (!file_put_contents($meta_path, json_encode($metadata))) {
        AJAXErr();
    }
    echo json_encode(array("status" => "success"));
    exit();
}

?>