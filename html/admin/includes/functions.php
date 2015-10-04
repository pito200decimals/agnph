<?php
// Main control panel for admin operations.

include_once(SITE_ROOT."includes/util/user.php");

function ComputePageAccess($user) {
    global $vars;
    $vars['canAdminSite'] = false;
    $vars['canAdminForums'] = false;
    $vars['canAdminGallery'] = false;
    $vars['canAdminFics'] = false;
    $vars['canAdminOekaki'] = false;
    $perms = str_split($user['Permissions']);
    foreach ($perms as $char) {
        switch ($char) {
            case 'A':
                $vars['canAdminSite'] = true;
                $vars['canAdminForums'] = true;
                $vars['canAdminGallery'] = true;
                $vars['canAdminFics'] = true;
                $vars['canAdminOekaki'] = true;
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
                $vars['canAdminOekaki'] = true;
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
        Redirect("/admin/forums/");
    }
    if ($vars['canAdminGallery']) {
        Redirect("/admin/gallery/");
    }
    if ($vars['canAdminFics']) {
        Redirect("/admin/fics/");
    }
    if ($vars['canAdminOekaki']) {
        Redirect("/admin/oekaki/");
    }
    RenderErrorPage("Not authorized to access this page");
}

?>