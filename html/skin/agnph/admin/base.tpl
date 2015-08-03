{% extends 'base.tpl' %}

{% block styles %}
    {#<link rel="stylesheet" type="text/css" href="{{ skinDir }}/admin/style.css" />#}
{% endblock %}

{% block section_navigation %}
    <ul class="section-nav">
        {% if canAdminSite %}<li><a href="/admin/">Site</a></li>{% endif %}
        {% if canAdminForums %}<li><a href="/admin/forums/">Forums</a></li>{% endif %}
        {% if canAdminGallery %}<li><a href="/admin/gallery/">Gallery</a></li>{% endif %}
        {% if canAdminFics %}<li><a href="/admin/fics/">Fics</a></li>{% endif %}
    </ul>
    <div class="Clear">&nbsp;</div>
    {% block sub_section_navigation %}
    {% endblock %}
{% endblock %}

{% block content %}
    <h3>Administrator Control Panel</h3>
{% endblock %}
