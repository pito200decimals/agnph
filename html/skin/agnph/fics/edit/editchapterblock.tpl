{% block chapterMCESetup %}
    tinymce.init({
        selector: "textarea#chapnotes",
        plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
        target_list: [ {title: 'New page', value: '_blank'} ],
        toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
        contextmenu: "image link | hr",
        autoresize_max_height: 200,
        resize: false,
        menubar: false
    });
    tinymce.init({
        selector: "textarea#chaptext",
        plugins: [ "paste", "link", "autoresize", "hr", "wordcount", "code", "contextmenu", "emoticons", "fullscreen", "preview", "image", "searchreplace", "textcolor" ],
        target_list: [ {title: 'New page', value: '_blank'} ],
        toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code fullscreen preview",
        contextmenu: "image link | hr",
        autoresize_max_height: 500,
        resize: false,
        setup: function(editor) {
          editor.on('FullscreenStateChanged', function(e) {
            $(document).scrollTop($("#chaptertextanchor").offset().top);
          });
        }
    });
    tinymce.init({
        selector: "textarea#chapendnotes",
        plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
        target_list: [ {title: 'New page', value: '_blank'} ],
        toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
        contextmenu: "image link | hr",
        autoresize_max_height: 200,
        resize: false,
        menubar: false
    });
{% endblock %}

{% block editchapter %}
    <input type="hidden" name="chapternum" value="{{ chapternum }}" />
    <input type="hidden" name="chapterid" value="{{ chapterid }}" />
    <p><label>Chapter Title:</label><input type="text" name="chaptertitle" value="{{ chaptertitle }}" /></p>
    <p><label>Chapter Notes:</label>
    <textarea id="chapnotes" name="chapternotes">
        {% autoescape false %}
            {{ chapternotes }}
        {% endautoescape %}
    </textarea></p>
    <p><a id="chaptertextanchor" /><label>Chapter Text:</label>
    <textarea id="chaptext" name="chaptertext">
        {% autoescape false %}
            {{ chaptertext}}
        {% endautoescape %}
    </textarea></p>
    <p><label>Chapter End Notes:</label>
    <textarea id="chapendnotes" name="chapterendnotes">
        {% autoescape false %}
            {{ chapterendnotes}}
        {% endautoescape %}
    </textarea></p>
{% endblock %}