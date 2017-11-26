{% block chapterMCESetup %}
    function CheckWallOfText(content) {
      var hasLargeParagraph = false;
      var hasBadDialogue = false;
      content = content.replace(/<br\s*\/?>/g, '</p><p>');
      $("<div>" + content + "</div>").find('p').each(function(i, p) {
        var text = $(p).text();
        if (text.length > 1200) {
          console.log(text);
          hasLargeParagraph = true;
        }
        var n_q = (text.match(/"/g) || []).length;
        var n_ldq = (text.match(/“/g) || []).length;
        var n_rdq = (text.match(/”/g) || []).length;
        if (n_q >= 6 || (n_ldq >= 3 && n_rdq >= 3)) {
          hasBadDialogue = true;
        }
      });
      if (hasLargeParagraph) {
        $('#length-warning').show();
      } else {
        $('#length-warning').hide();
      }
      if (hasBadDialogue) {
        $('#dialogue-warning').show();
      } else {
        $('#dialogue-warning').hide();
      }
    }
    tinymce.init({
        selector: "textarea#chapnotes",
        plugins: [ "paste", "link", "autoresize", "hr", "code", "emoticons", "image", "textcolor" ],
        target_list: [ {title: 'New page', value: '_blank'} ],
        toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
        contextmenu: false,
        autoresize_max_height: 300,
        resize: true,
        browser_spellcheck: true,
        menubar: false,
        relative_urls: false
    });
    tinymce.init({
        selector: "textarea#chaptext",
        plugins: [ "paste", "link", "autoresize", "hr", "wordcount", "code", "emoticons", "fullscreen", "preview", "image", "searchreplace", "textcolor" ],
        target_list: [ {title: 'New page', value: '_blank'} ],
        toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code fullscreen preview",
        contextmenu: false,
        autoresize_max_height: 500,
        resize: true,
        browser_spellcheck: true,
        setup: function(editor) {
          editor.on('FullscreenStateChanged', function(e) {
            $(document).scrollTop($("#chaptertextanchor").offset().top);
          }).on('KeyUp', function(e) {
            CheckWallOfText(editor.getContent());
          }).on('Init', function(e) {
            CheckWallOfText(editor.getContent());
          });
        },
        relative_urls: false
    });
    tinymce.init({
        selector: "textarea#chapendnotes",
        plugins: [ "paste", "link", "autoresize", "hr", "code", "emoticons", "image", "textcolor" ],
        target_list: [ {title: 'New page', value: '_blank'} ],
        toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
        contextmenu: false,
        autoresize_max_height: 300,
        resize: true,
        browser_spellcheck: true,
        menubar: false,
        relative_urls: false
    });
{% endblock %}

{% block editchapter %}
    <input type="hidden" name="chapternum" value="{{ chapternum }}" />
    <input type="hidden" name="chapterid" value="{{ chapterid }}" />
    <p>
        <label>Chapter Title:</label><input type="text" name="chaptertitle" value="{{ chaptertitle }}" />
    </p>
    <p>
        <label>Chapter Notes:</label>
        <textarea id="chapnotes" name="chapternotes">
            {% autoescape false %}
                {{ chapternotes }}
            {% endautoescape %}
        </textarea>
    </p>
    <p>
        <a id="chaptertextanchor"></a><label>Chapter Text:</label>
        <textarea id="chaptext" name="chaptertext">
            {% autoescape false %}
                {{ chaptertext}}
            {% endautoescape %}
        </textarea>
        <div id="length-warning" class="wall-of-text-warning">
          <p>
            <strong>Warning:</strong> You seem to have a large wall-of-text. You should format your story properly with line/paragraph breaks before saving.
          </p>
        </div>
        <div id="dialogue-warning" class="wall-of-text-warning">
          <p>
            <strong>Warning:</strong> You seem to have some mis-formatted dialogue. Dialogue lines spoken by multiple people should each be their own <em>separate</em> paragraph. You should fix this before saving.
          </p>
        </div>
    </p>
    <p>
        Or upload word document:
        <input type="file" name="chapter-file" accept="application/vnd.openxmlformats-officedocument.wordprocessingml.document" />
    </p>
    <p>
        <label>Chapter End Notes:</label>
        <textarea id="chapendnotes" name="chapterendnotes">
            {% autoescape false %}
                {{ chapterendnotes}}
            {% endautoescape %}
        </textarea>
    </p>
{% endblock %}