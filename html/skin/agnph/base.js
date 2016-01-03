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
        $(this).toggleClass("nav-dropdown-tray");
        if (toggled_visible_mobile) {
            $(".navigation_left").removeAttr("style");
        } else {
            $(".navigation_left").css("display", "inherit");
        }
        toggled_visible_mobile = !toggled_visible_mobile;
        return false;
    });
    
    // Set up font size switcher cookie.
    function setZoom(zoom) {
        var found = false;
        var options = $(".font-size-select").children().each(function() {
            if (zoom == $(this).text()) {
                found = true;
                $(this).prop("selected", true);
                setCookie("zoom", zoom);
                refreshZoom(zoom);
            }
        });
        if (!found) setZoom("100%");
    }

    function refreshZoom(zoom) {
        $(".font-scalable").children(":not(.not-font-scalable)").css("font-size", zoom);
    }
    setZoom(getCookie("zoom"));
    $(".font-size-select").change(function() {
        setZoom($(this).val());
    });
    $(".font-size-switcher").show();
});

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}
function setCookie(cname, cvalue) {
    var d = new Date();
    d.setTime(d.getTime() + (365*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
}