{% extends 'admin/log_base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li><a href="/admin/fics/">Settings</a></li>
        <li><a href="/admin/fics/tags/">Tags</a></li>
        <li class="selected-admin-tab"><a href="/admin/fics/log/">Log</a></li>
    </ul>
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