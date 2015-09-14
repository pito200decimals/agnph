{% extends 'admin/log_base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li id=""><a href="/admin/">Settings</a></li>
        <li id="selected-site-tab"><a href="/admin/log/">Log</a></li>
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