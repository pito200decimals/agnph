{% extends 'admin/tags/base.tpl' %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li><a href="/admin/fics/">Settings</a></li>
        <li id="selected-fics-tab"><a href="/admin/fics/tags/">Tags</a></li>
    </ul>
{% endblock %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ asset('/fics/style.css') }}" />
    <style>
        #tag-container label {
            display: inline-block;
            width: 100px;
        }
        #fics-admin-tab {
            background-color: rgb(191,223,255);
        }
        #selected-fics-tab {
            background-color: rgb(191,223,255);
        }
    </style>
{% endblock %}


{% block section %}Fics{% endblock %}
{% block type_list %}["Category", "Species", "Warning", "Character", "Series", "General"]{% endblock %}
