{% extends 'admin/base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li class="selected-admin-tab"><a href="/admin/">Settings</a></li>
        <li><a href="/admin/log/">Log</a></li>
    </ul>
{% endblock %}

{% block styles %}
    {{ parent() }}
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
            menubar: false,
            relative_urls: false
        });
    </script>
{% endblock %}

{% block content %}
    <h3>Site Administrator Control Panel</h3>
    {{ block('banner') }}
    <form method="POST" accept-encoding="UTF-8">
        <table>
            <tr><td><label>Site Welcome Message:</label></td><td></td></tr>
            <tr><td colspan="2"><textarea name="site-welcome-message">{{ site_welcome_message }}</textarea></td></tr>
            <tr><td><label>Registration agreement:</label></td><td></td></tr>
            <tr><td colspan="2"><textarea name="register-message">{{ register_message }}</textarea></td></tr>
            <tr><td><label>Duration of Bans:</label></td><td><input name="short-ban-duration" value="{{ short_ban_duration }}" /></td></tr>
            <tr><td><label>Maintenance Mode:</label></td><td><input type="checkbox" name="maintenance-mode" value="yes" {% if is_maintenance_mode %}checked {% endif %}/></td></tr>
            <tr><td><label>Board for News Posts:</label></td><td><input name="news-posts-board" type="text" value="{{ news_posts_board }}" /></td></tr>
            <tr><td><label>Max news posts:</label></td><td><input name="max-news-posts" type="text" value="{{ max_news_posts }}" /></td></tr>
            <tr><td><label>Login screen notification:</label></td><td></td></tr>
            <tr><td colspan="2"><textarea name="login-message">{{ login_message }}</textarea></td></tr>
        </table>
        <input type="submit" name="submit" value="Save Changes" />
    </form>
{% endblock %}
