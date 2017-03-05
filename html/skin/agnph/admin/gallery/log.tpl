{% extends 'admin/log_base.tpl' %}

{% block sub_section_navigation %}
    <li><a href="/admin/gallery/">Settings</a></li>
    <li><a href="/admin/gallery/tags/">Tags</a></li>
    <li><a href="/admin/gallery/edit-history/">Edit History</a></li>
    <li><a href="/admin/gallery/description-history/">Description History</a></li>
    <li class="selected-admin-tab"><a href="/admin/gallery/log/">Log</a></li>
{% endblock %}

{% block styles %}
    {{ parent() }}
    <style>
        td {
            vertical-align: top;
            padding-bottom: 10px;
        }
    </style>
{% endblock %}