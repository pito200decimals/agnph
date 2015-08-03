{% extends 'admin/tags/base.tpl' %}

{% block styles %}
    {#<link rel="stylesheet" type="text/css" href="{{ skinDir }}/admin/style.css" />#}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <style>
        #tag-container label {
            display: inline-block;
            width: 100px;
        }
    </style>
{% endblock %}

{% block sub_section_navigation %}
    <ul class="section-nav">
        <li><a href="/admin/gallery/">Settings</a></li>
        <li><a href="/admin/gallery/tags/">Tags</a></li>
    </ul>
{% endblock %}

{% block section %}Gallery{% endblock %}
{% block type_list %}["Artist", "Character", "Copyright", "General", "Species"]{% endblock %}
