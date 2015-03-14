<?php
// Functions for handling files.

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
    $mode = "w";
    if ($append) {
        $mode = "a";
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
    if (!isset($_FILES['upfile']['error']) || is_array($_FILES['upfile']['error'])) return false;
    switch ($_FILES['upfile']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
        default:
            return false;
    }
    if ($_FILES['upfile']['size'] > $max_size) return false;
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === ($ext = array_search($finfo->file($_FILES['upfile']['tmp_name']), array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ), true))) return false;

    $file_path = sprintf(SITE_ROOT.'uploads/%s.%s', md5_file($_FILES['upfile']['tmp_name']));
    if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $file_path, $ext)) return false;
    
    $tmp_path = $file_path;
    return true;
}
?>