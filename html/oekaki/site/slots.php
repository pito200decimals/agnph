<?php
// Creates metadata json ajax response for all slots.

define("SITE_ROOT", "../../");
include_once(SITE_ROOT."ajax_header.php");
include_once(SITE_ROOT."includes/constants.php");
include_once(SITE_ROOT."oekaki/site/includes/functions.php");
include_once(SITE_ROOT."gallery/includes/image.php");  // For image resize functions.

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
    $meta_path = $path.OEKAKI_METADATA_FILE_NAME;
    $thumb_path = $path.OEKAKI_THUMB_FILE_NAME;

    function CreateImageThumb($base64, $path) {
        $expected_base64_prefix = "data:image/png;base64,";
        if (!startsWith($base64, $expected_base64_prefix)) return false;
        $base64_data = substr($base64, strlen($expected_base64_prefix));
        $img = new SimpleImage();
        $img->loadFromBase64($base64_data);
        if ($img->getWidth() >= $img->getHeight()) {
            if ($img->getWidth() > MAX_OEKAKI_IMAGE_THUMB_SIZE) {
                $img->resizeToWidth(MAX_OEKAKI_IMAGE_THUMB_SIZE);
            }
        } else {
            if ($img->getHeight() > MAX_OEKAKI_IMAGE_THUMB_SIZE) {
                $img->resizeToHeight(MAX_OEKAKI_IMAGE_THUMB_SIZE);
            }
        }
        $img->save($path);
    }
    function CheckImageData($data) {
        $expected_base64_prefix = "data:image/png;base64,";
        if (!startsWith($data, $expected_base64_prefix)) return false;
        $base64_data = substr($data, strlen($expected_base64_prefix));
        if (getimagesizefromstring(base64_decode($base64_data)) === FALSE) {
            return false;
        }
        return true;
    }

    if (!IsMetadataValid($metadata)) {
        AJAXErr();
    }
    // Check for image data.
    foreach ($metadata['layers'] as $layer) {
        if (!isset($layer['data'])) AJAXErr();
        if (!CheckImageData($layer['data'])) AJAXErr();
    }
    if (!CheckImageData($metadata['imageData'])) AJAXErr();
    $metadata = SanitizeMetadata($metadata);
    if (!file_exists($path)) {
        mkdir($path, 0777, true /* recursive */);
    }
    if (!file_put_contents($meta_path, json_encode($metadata))) {
        AJAXErr();
    }
    // Save thumbnail.
    CreateImageThumb($metadata['imageData'], $thumb_path);
    echo json_encode(array("status" => "success"));
    exit();
}

?>