{% extends 'admin/log_base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li><a href="/admin/gallery/">Settings</a></li>
        <li><a href="/admin/gallery/tags/">Tags</a></li>
        <li><a href="/admin/gallery/edit-history/">Edit History</a></li>
        <li><a href="/admin/gallery/description-history/">Description History</a></li>
        <li id="selected-gallery-tab"><a href="/admin/gallery/log/">Log</a></li>
    </ul>
{% endblock %}

{% block styles %}
    {{ parent() }}
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