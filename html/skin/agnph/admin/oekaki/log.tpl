{% extends 'admin/log_base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li><a href="/admin/oekaki/">Settings</a></li>
        <li id="selected-oekaki-tab"><a href="/admin/oekaki/log/">Log</a></li>
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