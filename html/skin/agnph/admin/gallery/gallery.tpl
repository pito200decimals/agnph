{% extends 'admin/base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li id="selected-gallery-tab"><a href="/admin/gallery/">Settings</a></li>
        <li><a href="/admin/gallery/tags/">Tags</a></li>
        <li><a href="/admin/gallery/edit-history/">Edit History</a></li>
        <li><a href="/admin/gallery/description-history/">Description History</a></li>
    </ul>
{% endblock %}

{% block styles %}
    <style>
        td {
            vertical-align: top;
            padding-bottom: 10px;
        }
        #gallery-admin-tab {
            background-color: rgb(191,223,255);
        }
        #selected-gallery-tab {
            background-color: rgb(191,223,255);
        }
    </style>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}

{% block content %}
    <h3>Gallery Administrator Control Panel</h3>
    {{ block('banner') }}
    <form action="" method="POST" accept-encoding="UTF-8">
        TODO: Main gallery options.
        <input type="submit" value="Save Changes" />
    </form>
{% endblock %}
