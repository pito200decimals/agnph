{% extends 'base.tpl' %}

{% block section_navigation %}
    <ul class="section-nav">
        {% if canAdminSite %}<li id="site-admin-tab" ><a href="/admin/">Site</a></li>{% endif %}
        {% if canAdminForums %}<li id="forums-admin-tab" >Forums</li>{% endif %}
        {% if canAdminGallery %}<li id="gallery-admin-tab" ><a href="/admin/gallery/">Gallery</a></li>{% endif %}
        {% if canAdminFics %}<li id="fics-admin-tab" ><a href="/admin/fics/">Fics</a></li>{% endif %}
    </ul>
    <div class="Clear">&nbsp;</div>
    {% block sub_section_navigation %}
    {% endblock %}
{% endblock %}

{% block content %}
    <h3>Administrator Control Panel</h3>
{% endblock %}
