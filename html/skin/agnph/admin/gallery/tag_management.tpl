{% extends 'admin/tags/base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li><a href="/admin/gallery/">Settings</a></li>
        <li id="selected-gallery-tab"><a href="/admin/gallery/tags/">Tags</a></li>
        <li><a href="/admin/gallery/edit-history/">Edit History</a></li>
        <li><a href="/admin/gallery/description-history/">Description History</a></li>
    </ul>
{% endblock %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
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
