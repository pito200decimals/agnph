{% extends 'admin/log_base.tpl' %}

{% block sub_section_navigation %}
    <li><a href="/admin/oekaki/">Settings</a></li>
    <li class="selected-admin-tab"><a href="/admin/oekaki/log/">Log</a></li>
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