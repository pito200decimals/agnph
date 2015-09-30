{% extends 'admin/base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li id="selected-oekaki-tab"><a href="/admin/oekaki/">Settings</a></li>
        <li><a href="/admin/oekaki/log/">Log</a></li>
    </ul>
{% endblock %}

{% block styles %}
    {{ parent() }}
    <style>
        td {
            vertical-align: top;
            padding-bottom: 10px;
        }
        #oekaki-admin-tab {
            background-color: rgb(191,223,255);
        }
        #selected-oekaki-tab {
            background-color: rgb(191,223,255);
        }
    </style>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}

{% block content %}
    <h3>Oekaki Administrator Control Panel</h3>
    {{ block('banner') }}
    <form action="" method="POST" accept-encoding="UTF-8">
        <table>
            <tr><td><label>Board for News Posts:</label></td><td><input name="news-posts-board" type="text" value="{{ news_posts_board }}" /></td></tr>
        </table>
        <input type="submit" name="submit" value="Save Changes" />
    </form>
{% endblock %}
