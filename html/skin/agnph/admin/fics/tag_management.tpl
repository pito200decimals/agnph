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

{% block section %}Fics{% endblock %}
{% block type_list %}["Category", "Species", "Warning", "Character", "Series", "General"]{% endblock %}
