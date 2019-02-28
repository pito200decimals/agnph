{% extends 'base.tpl' %}

{% block section_navigation %}
    <ul class="section-nav">
        {% if canAdminSite %}<li{% if admin_section=="site" %} class="selected-admin-tab" {% endif %}><a href="/admin/">Site</a></li>{% endif %}
        {% if canAdminForums %}<li{% if admin_section=="forums" %} class="selected-admin-tab" {% endif %}><a href="/admin/forums/">Forums</a></li>{% endif %}
        {% if canAdminGallery %}<li{% if admin_section=="gallery" %} class="selected-admin-tab" {% endif %}><a href="/admin/gallery/">Gallery</a></li>{% endif %}
        {% if canAdminFics %}<li{% if admin_section=="fics" %} class="selected-admin-tab" {% endif %}><a href="/admin/fics/">Fics</a></li>{% endif %}
        {% if canAdminOekaki %}<li{% if admin_section=="oekaki" %} class="selected-admin-tab" {% endif %}><a href="/admin/oekaki/">Oekaki</a></li>{% endif %}
        <li class="divider"></li>
        {% block sub_section_navigation %}
        {% endblock %}
    </ul>
{% endblock %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/admin/color.css')|raw }}
{% endblock %}

{% block content %}
    <h3>Administrator Control Panel</h3>
{% endblock %}
