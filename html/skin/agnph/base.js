$(document).ready(function() {
    $("#account-menu-button").hover(function() {
        $("#account-menu-button").addClass("nav-menu-tray-button");
        $("#account-menu").addClass("tray-open");
    }, function() {
        $("#account-menu-button").removeClass("nav-menu-tray-button");
        $("#account-menu").removeClass("tray-open");
    });
    /* For mobile, on touch, toggle dropdown and ignore click. */
    var supports_touch = ((document.ontouchstart===null)?true:false);
    var account_link_flag = false;
    $("#account-menu-button").bind("touchstart click", function(e) {
        if (supports_touch) {
            e.preventDefault();
            if (!account_link_flag) {
                account_link_flag = true;
                setTimeout(function() { account_link_flag = false; }, 100);
                $("#account-menu").toggleClass("tray-open");
                $("#account-menu-button").toggleClass("tray-open");
            }
            return false;
        }
    });
    $("#main-menu-icon").bind("touchstart click", function(e) {
        e.preventDefault();
        $(this).toggleClass("nav-menu-tray-button");
        $("#main-nav-menu").toggleClass("tray-open");
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