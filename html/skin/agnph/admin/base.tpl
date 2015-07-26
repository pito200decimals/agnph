{% extends 'base.tpl' %}

{% block styles %}
    {#<link rel="stylesheet" type="text/css" href="{{ skinDir }}/admin/style.css" />#}
{% endblock %}

{% block section_navigation %}
    <h3>Administrator Control Panel</h3>
    <ul class="section-nav">
        {% if canAdminSite %}<li><a href="/admin/">Site</a></li>{% endif %}
        {% if canAdminForums %}<li><a href="/admin/forums/">Forums</a></li>{% endif %}
        {% if canAdminGallery %}<li><a href="/admin/gallery/">Gallery</a></li>{% endif %}
        {% if canAdminFics %}<li><a href="/admin/fics/">Fics</a></li>{% endif %}
    </ul>
{% endblock %}

{% block content %}
{% endblock %}
