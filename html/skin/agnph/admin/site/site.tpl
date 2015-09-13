{% extends 'admin/base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li id="selected-site-tab"><a href="/admin/">Settings</a></li>
    </ul>
{% endblock %}

{% block styles %}
    <style>
        td {
            vertical-align: top;
            padding-bottom: 10px;
        }
        #site-admin-tab {
            background-color: rgb(191,223,255);
        }
        #selected-site-tab {
            background-color: rgb(191,223,255);
        }
    </style>
{% endblock %}

{% block scripts %}
    {{ parent() }}
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
    <h3>Site Administrator Control Panel</h3>
    {{ block('banner') }}
    <form method="POST" accept-encoding="UTF-8">
        <table>
            <tr><td>Maintenance Mode</td><td><input type="checkbox" name="maintenance-mode" value="yes" {% if is_maintenance_mode %}checked {% endif %}/></td></tr>
        </table>
        <input type="submit" name="submit" value="Save Changes" />
    </form>
{% endblock %}
