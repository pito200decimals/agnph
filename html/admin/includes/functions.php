<?php
// Main control panel for admin operations.

include_once(SITE_ROOT."includes/util/user.php");

function ComputePageAccess($user) {
    global $vars;
    $vars['canAdminSite'] = false;
    $vars['canAdminForums'] = false;
    $vars['canAdminGallery'] = false;
    $vars['canAdminFics'] = false;
    $perms = str_split($user['Permissions']);
    foreach ($perms as $char) {
        switch ($char) {
            case 'A':
                $vars['canAdminSite'] = true;
                $vars['canAdminForums'] = true;
                $vars['canAdminGallery'] = true;
                $vars['canAdminFics'] = true;
                break;
            case 'R':
                $vars['canAdminForums'] = true;
                break;
            case 'G':
                $vars['canAdminGallery'] = true;
                break;
            case 'F':
                $vars['canAdminFics'] = true;
                break;
            case 'O':
                // TODO: Oekaki permissions.
                break;
            default:
                break;
        }
    }
}

function DoRedirect() {
    global $vars;
    // Redirect to another admin control panel page.
    if ($vars['canAdminForums']) {
        header("Location: /admin/forums/");
        return;
    }
    if ($vars['canAdminGallery']) {
        header("Location: /admin/gallery/");
        return;
    }
    if ($vars['canAdminFics']) {
        header("Location: /admin/fics/");
        return;
    }
    RenderErrorPage("Not authorized to access this page");
}

?>