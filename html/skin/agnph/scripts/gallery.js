$(document).ready(function() {
    tinymce.init({
        selector: "textarea.commenttextbox",
        plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor", "spoiler" ],
        target_list: [ {title: 'New page', value: '_blank'} ],
        toolbar: "undo redo | bold italic underline | bullist numlist | image link | code blockquote spoiler",
        contextmenu: "image link | hr",
        autoresize_max_height: 300,
        resize: true,
        menubar: false,
        relative_urls: false,
        content_css: COMMENTS_STYLE_CSS,
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