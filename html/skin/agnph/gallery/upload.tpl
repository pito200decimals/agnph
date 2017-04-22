{% extends 'gallery/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/upload-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/tag-complete-style.css') }}" />
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script src="{{ asset('/scripts/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('/scripts/tag-complete.js') }}"></script>
    <script type="text/javascript">
        var OnEditSubmit;
        (function() {
            var tag_search_url = '/gallery/tagsearch/';
            function GetPreclass(pre) {
                var preclass = null;
                if (pre.toLowerCase() == 'artist') {
                    preclass = 'atypetag';
                }
                if (pre.toLowerCase() == 'copyright') {
                    preclass = 'btypetag';
                }
                if (pre.toLowerCase() == 'character') {
                    preclass = 'ctypetag';
                }
                if (pre.toLowerCase() == 'species') {
                    preclass = 'dtypetag';
                }
                if (pre.toLowerCase() == 'general') {
                    preclass = 'mtypetag';
                }
                return preclass;
            }
            var fns = SetUpTagCompleter(tag_search_url, GetPreclass, ".g");
            OnEditSubmit = fns.OnEditSubmit;
        })();
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

    {# script for fancy tagger UI #}
    {% if not user.PlainGalleryTagging %}
        <script>
            $(document).ready(function() {
                {% for category in post.tagCategories %}
                    {% for tag in category.tags %}
                        AddTag('{{ tag.Name }}', '{{ tag.Type|lower }}');
                    {% endfor %}
                {% endfor %}
            });
        </script>
    {% endif %}
{% endblock %}

{% block content %}
    <div class="uploadpanel">
        <h3>Upload</h3>
        <form action="" method="post" enctype="multipart/form-data" accept-charset="UTF-8" onsubmit="OnEditSubmit()">
            <table>
                <tr>
                    <td><label class="formlabel">File</label></td>
                    <td><input id="imgbrowse" class="textbox" type="file" name="file" accept="image/jpeg,image/png,image/gif,application/x-shockwave-flash,video/webm" /></td>
                </tr>
                <tr>
                    <td><label class="formlabel">Source</label></td>
                    <td><input id="imgsource" class="textbox" type="textbox" size=35 name="source" /></td>
                </tr>
                {% if not user.PlainGalleryTagging %}
                    <tr>
                        <td><label class="formlabel">Tags</label></td>
                        <td><ul class="g autocomplete-tag-list"></ul><textarea class="g autocomplete-tags" name="tags" hidden>{{ post.tagstring }}</textarea></td>
                    </tr>
                    <tr>
                        <td><label class="formlabel">&nbsp;</label></td>
                        <td><input type="text" class="g textbox autocomplete-tag-input" /></td>
                    </tr>
                {% else %}
                    <tr>
                        <td><label class="formlabel">Tags</label></td>
                        <td><textarea id="tags" class="textbox" name="tags">{{ post.tagstring }}</textarea></td>
                    </tr>
                {% endif %}
                <tr>
                    <td><label class="formlabel">Description</label></td>
                    <td><textarea id="desc" class="textbox" name="description"></textarea></td>
                </tr>
                <tr>
                    <td><label class="formlabel">Parent</label></td>
                    <td><input id="parent" class="textbox" type="text" name="parent" /></td>
                </tr>
                <tr>
                    <td><label class="formlabel">Rating</label></td>
                    <td>
                        <span class="radio-button-group"><input id="rating-e-box" name="rating" type="radio" value="e" required /><label for="rating-e-box">Explicit</label></span>
                        <span class="radio-button-group"><input id="rating-q-box" name="rating" type="radio" value="q" required /><label for="rating-q-box">Questionable</label></span>
                        <span class="radio-button-group"><input id="rating-s-box" name="rating" type="radio" value="s" required /><label for="rating-s-box">Safe</label></span>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="Upload" /></td>
                </tr>
            </table>
        </form>
    </div>
    <div class="previewpanel" id="previewpanel">
        <h3>Preview</h3>
        <img id="previewimg" src="/gallery/data/80/14/8014cdf559ca76698f7c1a2fbcd154dc.png" />
    </div>
{% endblock %}
