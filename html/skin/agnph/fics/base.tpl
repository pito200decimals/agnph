{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
{% endblock %}

{% block section_navigation %}
    <ul class="section-nav">
        <li><a href="/fics/">Index</a></li>
        <li><a href="/fics/browse/">Stories</a></li>
        <li><a href="/fics/authors/">Authors</a></li>
        <li><a href="/fics/tags/">Tags</a></li>
        {% if user %}<li><a href="/fics/edit_story.php?action=create">Upload</a></li>{% endif %}
        {% if user %}<li><a href="/fics/user/{{ user.UserId }}/">My Stories</a></li>{% endif %}
        <li id="fics-search">
            <form action="/fics/search/" accept-charset="UTF-8">
                <input class="search" name="search" value="{{ searchTerms }}" type="text" required placeholder="Search" onfocus="javascript:$(this).attr('placeholder', '');" onblur="javascript:$(this).attr('placeholder', 'Search');"  />
            </form>
        </li>
    </ul>
{% endblock %}

{% block content %}
    {% block ficscontent %}
    {% endblock %}
{% endblock %}
