<?php
// General image utility classes and functions.

include_once(SITE_ROOT."../lib/getid3/getid3.php");

class SimpleImage
{
    var $image;
    var $image_type;
    function load($filename)
    {
        $image_info       = getimagesize($filename);
        $this->image_type = $image_info[2];
        if ($this->image_type == IMAGETYPE_JPEG) {
            $this->image = imagecreatefromjpeg($filename);
        } elseif ($this->image_type == IMAGETYPE_GIF) {
            $this->image = imagecreatefromgif($filename);
        } elseif ($this->image_type == IMAGETYPE_PNG) {
            $this->image = imagecreatefrompng($filename);
        }
    }
    function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image, $filename);
        }
        if ($permissions != null) {
            chmod($filename, $permissions);
        }
    }
    function output($image_type = IMAGETYPE_JPEG)
    {
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image);
        }
    }
    function getWidth()
    {
        return imagesx($this->image);
    }
    function getHeight()
    {
        return imagesy($this->image);
    }
    function resizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }
    function resizeToWidth($width)
    {
        $ratio  = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }
    function scale($scale)
    {
        $width  = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        $this->resize($width, $height);
    }
    function resize($width, $height)
    {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }
}

function GetImagePath($md5, $ext) {
    $path = "gallery/data/";
    $path .= mb_substr($md5, 0, 2)."/";
    $path .= mb_substr($md5, 2, 2)."/";
    $path .= "$md5.$ext";
    return $path;
}

function GetThumbPath($md5, $ext) {
    switch ($ext) {
        case 'jpg':
        case 'png':
        case 'gif':
            break;
        case 'swf':
            return "images/swf-preview.png";
        case 'webm':
            return "images/webm-preview.png";
    }
    $path = "gallery/data/thumb/";
    $path .= mb_substr($md5, 0, 2)."/";
    $path .= mb_substr($md5, 2, 2)."/";
    $path .= "$md5.jpg";
    return $path;
}

function GetPreviewPath($md5, $ext) {
    $path = "gallery/data/preview/";
    $path .= mb_substr($md5, 0, 2)."/";
    $path .= mb_substr($md5, 2, 2)."/";
    $path .= "$md5.$ext";
    return $path;
}

function CreateThumbnailFile($md5, $ext) {
    $image_path = GetSystemImagePath($md5, $ext);
    $thumb_path = GetSystemThumbPath($md5, $ext);
    $oldmask = umask(0);
    mkdirs(dirname(GetSiteThumbPath($md5, $ext)));
    umask($oldmask);
    $image = new SimpleImage();
    $image->load($image_path);
    // Always create thumbnail file.
    if ($image->getWidth() > $image->getHeight()) {
        $image->resizeToWidth(MAX_IMAGE_THUMB_SIZE);
    } else {
        $image->resizeToHeight(MAX_IMAGE_THUMB_SIZE);
    }
    $image->save($thumb_path);
    return $thumb_path;
}

function CreatePreviewFile($md5, $ext) {
    $image_path = GetSystemImagePath($md5, $ext);
    $preview_path = GetSystemPreviewPath($md5, $ext);
    $oldmask = umask(0);
    mkdirs(dirname(GetSitePreviewPath($md5, $ext)));
    umask($oldmask);
    $image = new SimpleImage();
    $image->load($image_path);
    if (($image->getWidth() > MAX_IMAGE_PREVIEW_SIZE || $image->getHeight() > MAX_IMAGE_PREVIEW_SIZE)) {
        if ($image->getWidth() > $image->getHeight()) {
            $image->resizeToWidth(MAX_IMAGE_PREVIEW_SIZE);
        } else {
            $image->resizeToHeight(MAX_IMAGE_PREVIEW_SIZE);
        }
        $image->save($preview_path);
    } else {
        $preview_path = $image_path;
    }
    return $preview_path;
}
?>