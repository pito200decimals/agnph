{% extends 'admin/base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li id="selected-fics-tab"><a href="/admin/fics/">Settings</a></li>
        <li><a href="/admin/fics/tags/">Tags</a></li>
    </ul>
{% endblock %}

{% block styles %}
    <style>
        td {
            vertical-align: top;
            padding-bottom: 10px;
        }
        #fics-admin-tab {
            background-color: rgb(191,223,255);
        }
        #selected-fics-tab {
            background-color: rgb(191,223,255);
        }
    </style>
{% endblock %}

{% block scripts %}
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <script type="text/javascript">
        tinymce.init({
            selector: "textarea",
            plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
            target_list: [ {title: 'New page', value: '_blank'} ],
            toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
            contextmenu: "image link | hr",
            autoresize_max_height: 200,
            resize: false,
            menubar: false
        });
    </script>
{% endblock %}

{% block content %}
    <h3>Fics Administrator Control Panel</h3>
    {{ block('banner') }}
    <form method="POST" accept-encoding="UTF-8">
        <table>
            <tr><td><label>Welcome Message:</label></td><td></td></tr>
            <tr><td colspan="2"><textarea name="welcome-message">{% autoescape false %}{{ welcome_message }}{% endautoescape %}</textarea></td></tr>
            <tr><td><label>Chapter Minimum Word Count:</label></td><td><input name="min-word-count" type="text" value="{{ min_word_count }}" /></td></tr>
        </table>
        <input type="submit" value="Save Changes" />
    </form>
{% endblock %}
