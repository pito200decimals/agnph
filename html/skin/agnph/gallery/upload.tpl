{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/upload-style.css" />
{% endblock %}

{% block scripts %}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    function ResetImage() {
        var file = $("#imgbrowse")[0];
        if (file.files && file.files[0]) {
            var reader = new FileReader();
            reader.onload = function() {
                $("#previewimg").attr("src", reader.result);
                $("#previewpanel").removeAttr("style");
            };
            reader.onerror = function() {
                $("#previewimg").attr("src", $("#imgsource").val());
                $("#previewpanel").removeAttr("style");
            };
            reader.readAsDataURL(file.files[0]);
            return;
        }
        $("#previewimg").attr("src", $("#imgsource").val());
        $("#previewpanel").removeAttr("style");
    }
    $("#previewpanel").hide();
    $("#imgbrowse").on("change mousedown", ResetImage);
    $("#imgsource").on("change", ResetImage);
    $("#previewimg").error(function() {
        $("#previewpanel").hide();
    });
});
</script>
{% endblock %}

{% block content %}
    <div class="uploadpanel">
        <h3>Upload</h3>
        <form action="" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
            <label class="formlabel">File</label>           <input id="imgbrowse" class="textbox" type="file" name="file" accept="image/jpeg,image/png,image/gif,application/x-shockwave-flash,video/webm" /><br />
            <label class="formlabel">Source</label>         <input id="imgsource" class="textbox" type="textbox" size=35 name="source" /><br />
            <label class="formlabel">Tags</label>           <textarea class="textbox" name="tags" required></textarea><br />
            {#<label class="formlabel">Description</label>    <textarea id="desc" class="textbox" name="description"></textarea><br />#}
            <label class="formlabel">Parent</label>         <input id="parent" class="textbox" type="textbox" name="parent" /><br />
            <label class="formlabel">Rating</label>         <input name="rating" type="radio" value="e" /><label>Explicit</label>
                                                            <input name="rating" type="radio" checked value="q" /><label>Questionable</label>
                                                            <input name="rating" type="radio" value="s" /><label>Safe</label><br />
            <br />
            <input type="submit" value="Upload" />
        </form>
    </div>
    <div class="previewpanel" id="previewpanel">
        <h3>Preview</h3>
        <img id="previewimg" src="/gallery/data/80/14/8014cdf559ca76698f7c1a2fbcd154dc.png" />
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
