<?php
// Functions for handling files.

// Creates the directory containing the given path, and all parent directories as needed.
function mkdirs($site_dir_path) {
    if (substr($site_dir_path, 0, 1) == "/")
        $site_dir_path = substr($site_dir_path, 1, strlen($site_dir_path) - 1);
     if (file_exists(SITE_ROOT.$site_dir_path)) return;
    mkdir(SITE_ROOT.$site_dir_path, 0777, true);
}

function mksysdirs($sys_dir_path) {
     if (file_exists($sys_dir_path)) return;
    mkdir($sys_dir_path, 0777, true);
}

// Gets the file extension of the given path, or null if it doesn't have an extension.
function GetFileExtension($fname) {
    $path_parts = pathinfo($fname);
    if (isset($path_parts['extension']) && $path_parts['extension'] !== NULL) {
        return $path_parts['extension'];
    } else {
        return null;
    }
}

// Reads the entire contents of the given file.
// Returns true on success, false on failure.
function read_file($file_path, &$dest) {
    $handle = fopen($file_path, "r");
    if (!$handle) return false;
    $success = true;
    $dest = fread($handle, filesize($file_path));
    if (!$dest) $success = false;
    if (!fclose($handle)) $success = false;
    return $success;
}

// Writes to a file. Creates it if it doesn't exist. Fails if the parent directory doesn't exist.
// Returns true on success, false on failure.
function write_file($file_path, $contents, $append = false) {
    $mode = "w+";
    if ($append) {
        $mode = "a+";
    }
    $handle = fopen($file_path, $mode);
    if (!$handle) return false;
    $success = true;
    if (!fwrite($handle, $contents))  $success = false;
    if (!fclose($handle)) $success = false;
    return $success;
}

// Deletes the given file or directory (and all child files).
// TODO: Add return value with success.
function delete_files($target) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
        
        foreach( $files as $file )
        {
            delete_files( $file );      
        }
      
        rmdir( $target );
    } elseif(is_file($target)) {
        unlink( $target );  
    }
}

// Accepts a file upload. Returns true on success, false on failure (or no upload).
// Only accepts jpg, png and gif (for now).
// NOTE: Caller is responsible for deleting the temp file!
function accept_file_upload(&$tmp_path, $max_size = MAX_FILE_SIZE) {
    // NOTE: Code modelled after http://php.net/manual/en/features.file-upload.php
    if (!isset($_FILES['file']['error']) || is_array($_FILES['file']['error'])) {
        debug("1");
        return false;
    }
    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
        default:
            return false;
    }
    if ($_FILES['file']['size'] > $max_size) {
        debug("2");
        return false;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === ($ext = array_search($finfo->file($_FILES['file']['tmp_name']), array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ), true))) {
        debug("3");
        return false;
    }
    mkdirs("/uploads/temp/");
    $file_path = sprintf(SITE_ROOT."uploads/temp/%s.%s", md5_file($_FILES['file']['tmp_name']), $ext);
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) return false;
    
    $tmp_path = $file_path;
    return true;
}

function GetHumanReadableFileSize($num_bytes) {
    if ($num_bytes < 1024) return "$num_bytes B";
    $num_kb = $num_bytes / 1024.0;
    if ($num_kb < 1024) return sprintf("%.1f KB", $num_kb);
    $num_mb = $num_kb / 1024.0;
    return sprintf("%.1f MB", $num_mb);
}
?>