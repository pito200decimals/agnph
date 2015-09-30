{% extends 'admin/log_base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li><a href="/admin/forums/">Settings</a></li>
        <li id="selected-forums-tab"><a href="/admin/forums/log/">Log</a></li>
    </ul>
{% endblock %}

{% block styles %}
    {{ parent() }}
    <style>
        td {
            vertical-align: top;
            padding-bottom: 10px;
        }
        #forums-admin-tab {
            background-color: rgb(191,223,255);
        }
        #selected-forums-tab {
            background-color: rgb(191,223,255);
        }
    </style>
{% endblock %}