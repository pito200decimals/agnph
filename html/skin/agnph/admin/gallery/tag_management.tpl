{% extends 'admin/tags/base.tpl' %}

{% block sub_section_navigation %}
    <li><a href="/admin/gallery/">Settings</a></li>
    <li class="selected-admin-tab"><a href="/admin/gallery/tags/">Tags</a></li>
    <li><a href="/admin/gallery/edit-history/">Edit History</a></li>
    <li><a href="/admin/gallery/description-history/">Description History</a></li>
    <li><a href="/admin/gallery/log/">Log</a></li>
{% endblock %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/gallery/style.css')|raw }}
    <style>
        #tag-container label {
            display: inline-block;
            width: 100px;
        }
        #gallery-admin-tab {
            background-color: rgb(191,223,255);
        }
        #selected-gallery-tab {
            background-color: rgb(191,223,255);
        }
    </style>
{% endblock %}

{% block section %}Gallery{% endblock %}
{% block type_list %}["Artist", "Character", "Copyright", "General", "Species"]{% endblock %}
