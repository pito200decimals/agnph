{% extends 'admin/base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li><a href="/admin/gallery/">Settings</a></li>
        <li><a href="/admin/gallery/tags/">Tags</a></li>
    </ul>
{% endblock %}

{% block styles %}
    <style>
        td {
            vertical-align: top;
            padding-bottom: 10px;
        }
    </style>
{% endblock %}

{% block scripts %}
{% endblock %}

{% block content %}
    <h3>Gallery Administrator Control Panel</h3>
    {{ block('banner') }}
    <form action="" method="POST" accept-encoding="UTF-8">
        TODO: Main gallery options.
        <input type="submit" value="Save Changes" />
    </form>
{% endblock %}
