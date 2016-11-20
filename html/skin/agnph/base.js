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
    
    // Set up removing placeholder text in search boxes.
    $("div.search input[type='text'].search").focus(function() {
        var placeholder = $(this).attr("placeholder");
        $(this).attr("placeholder", "");
        $(this).off("blur").blur(function() {
            $(this).attr("placeholder", placeholder);
        });
    });
    
    // Maybe async-load all images.
    $("[data-src]").each(function(i, tag) {
        tag = $(tag);
        var src = tag.attr("data-src");
        var im = new Image();
        function OnLoad() {
            tag.attr("src", src);
        }
        im.onload = OnLoad;
        im.src = src;
        if (im.complete) {
            OnLoad();
        }
    });
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