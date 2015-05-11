{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
{% endblock %}

{% block content %}
    <ul class="ficsnav">
        <li><a href="/fics/">Index</a></li>
        <li><a href="/fics/browse/">Stories</a></li>
        <li><a href="/fics/authors/">Authors</a></li>
        <li><a href="/fics/tags/">Tags</a></li>
        {% if user %}<li><a href="/fics/user/{{ user.UserId }}/">My Stories</a></li>{% endif %}
        {% if user %}<li><a href="/fics/edit_story.php?action=create">Upload</a></li>{% endif %}
        <li>
            <form action="/fics/search/" accept-charset="UTF-8">
                <input class="search" name="search" value="{{ searchTerms }}" type="text" required />
            </form>
        </li>
    </ul>
    {% block ficscontent %}
    {% endblock %}
{% endblock %}
