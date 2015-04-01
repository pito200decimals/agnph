{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
{% endblock %}

{% block content %}
    <ul class="ficsnav">
        <li><a href="/fics/browse/">Browse</a></li>
        <li><a href="/fics/authors/">Authors</a></li>
        <li><a href="/fics/categories/">Categories</a></li>
        <li><a href="/fics/titles/">Titles</a></li>
        {% if user %}<li><a href="/fics/user/{{ user.UserId }}/">My Stories</a></li>{% endif %}
    </ul>
    {% block ficscontent %}
    {% endblock %}
{% endblock %}
