{% extends 'admin/tags/base.tpl' %}

{% block sub_section_navigation %}
    <li><a href="/admin/fics/">Settings</a></li>
    <li class="selected-admin-tab"><a href="/admin/fics/tags/">Tags</a></li>
    <li><a href="/admin/fics/log/">Log</a></li>
{% endblock %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/fics/style.css')|raw }}
    <style>
        #tag-container label {
            display: inline-block;
            width: 100px;
        }
    </style>
{% endblock %}


{% block section %}Fics{% endblock %}
{% block type_list %}["Category", "Species", "Warning", "Character", "Series", "General"]{% endblock %}
