{% block chapterMCESetup %}
    tinymce.init({
        selector: "textarea#chapnotes",
        plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
        target_list: [ {title: 'New page', value: '_blank'} ],
        toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
        contextmenu: "image link | hr",
        autoresize_max_height: 200,
        resize: false
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
        resize: false
    });
{% endblock %}

{% block editchapter %}
    <input type="hidden" name="chapterindex" value="{{ chapterindex }}" />
    <p><label>Chapter Title:</label><input type="textfield" name="chaptertitle" value="{{ chapter.Title }}" /></p>
    <p><label>Chapter Notes:</label>
    <textarea id="chapnotes" name="chapternotes">
        {% autoescape false %}
            {{ chapter.notes }}
        {% endautoescape %}
    </textarea></p>
    <p><a id="chaptertextanchor" /><label>Chapter Text:</label>
    <textarea id="chaptext" name="chaptertext">
        {% autoescape false %}
            {{ chapter.text}}
        {% endautoescape %}
    </textarea></p>
    <p><label>Chapter End Notes:</label>
    <textarea id="chapendnotes" name="chapterendnotes">
        {% autoescape false %}
            {{ chapter.endnotes}}
        {% endautoescape %}
    </textarea></p>
{% endblock %}