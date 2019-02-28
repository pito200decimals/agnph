$(document).ready(function() {
    $("#account-menu-button").hover(function() {
        $("#account-menu-button").addClass("nav-menu-tray-button");
        $("#account-menu").addClass("tray-open");
    }, function() {
        $("#account-menu-button").removeClass("nav-menu-tray-button");
        $("#account-menu").removeClass("tray-open");
    }).bind("touchstart", function(e) {
        if ($(e.target).closest("#account-menu").length == 0) {
            $("#account-menu").toggleClass("tray-open");
            $("#account-menu-button").toggleClass("tray-open");
            e.preventDefault();
            return false;
        }
    });
    $("#main-menu-icon").bind("touchstart click", function(e) {
        e.preventDefault();
        $(this).toggleClass("nav-menu-tray-button");
        $("#main-nav-menu").toggleClass("tray-open");
        return false;
    });
    
    /* Set up removing placeholder text in search boxes. */
    $("[type='text'][placeholder]").focus(function() {
        var placeholder = $(this).attr("placeholder");
        $(this).attr("placeholder", "");
        $(this).off("blur").blur(function() {
            $(this).attr("placeholder", placeholder);
        });
    });
    
    /* Maybe async-load all images. */
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
    $(".jsonly").show();
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