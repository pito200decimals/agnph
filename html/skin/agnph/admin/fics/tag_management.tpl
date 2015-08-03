{% extends 'admin/tags/base.tpl' %}

{% block styles %}
    {#<link rel="stylesheet" type="text/css" href="{{ skinDir }}/admin/style.css" />#}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <style>
        #tag-container label {
            display: inline-block;
            width: 100px;
        }
    </style>
{% endblock %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li><a href="/admin/fics/">Settings</a></li>
        <li><a href="/admin/fics/tags/">Tags</a></li>
    </ul>
{% endblock %}

{% block section %}Fics{% endblock %}
{% block type_list %}["Category", "Species", "Warning", "Character", "Series", "General"]{% endblock %}
