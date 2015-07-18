$(document).ready(function() {
    $("#account_link").hover(function() {
        $("#account_dropdown").addClass("nav-dropdown-tray");
        $("#account_link").addClass("nav-dropdown-tray");
    }, function() {
        $("#account_dropdown").removeClass("nav-dropdown-tray");
        $("#account_link").removeClass("nav-dropdown-tray");
    });
    /* For mobile, on touch, toggle dropdown and ignore click. */
    var supports_touch = ((document.ontouchstart===null)?true:false);
    var account_link_flag = false;
    $("#account_link>a").bind("touchstart click", function(e) {
        if (supports_touch) {
            e.preventDefault();
            if (!account_link_flag) {
                account_link_flag = true;
                setTimeout(function() { account_link_flag = false; }, 100);
                $("#account_dropdown").toggleClass("nav-dropdown-tray");
                $("#account_link").toggleClass("nav-dropdown-tray");
            }
            return false;
        }
    });
    var toggled_visible_mobile = false;
    $("#main_menu_icon").bind("touchstart click", function(e) {
        e.preventDefault();
        if (toggled_visible_mobile) {
            $(".navigation_left").removeAttr("style");
        } else {
            $(".navigation_left").css("display", "inherit");
        }
        toggled_visible_mobile = !toggled_visible_mobile;
        return false;
    });
});