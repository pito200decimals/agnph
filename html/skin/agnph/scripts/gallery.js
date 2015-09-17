$(document).ready(function() {
    tinymce.init({
        selector: "textarea.commenttextbox",
        plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
        target_list: [ {title: 'New page', value: '_blank'} ],
        toolbar: "undo redo | bold italic underline | bullist numlist | link",
        contextmenu: "image link | hr",
        autoresize_max_height: 150,
        resize: false,
        menubar: false
    });
    $("#commentbutton").click(function() {
        $("#commentbutton").hide();
        $("#commentform").show();
        $("html body").animate(
            { scrollTop: $("#commentform").offset().top },
            { duration: 0,
              complete: function() {
                tinyMCE.get("commenttextbox").focus();
            }});
    });
});