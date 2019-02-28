{% extends 'fics/base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/list-style.css')|raw }}
{% endblock %}

{% block sortArrow %}
    {% if orderParam == "desc" %}
        ▼
    {% else %}
        ▲
    {% endif %}
{% endblock %}

{% block content %}
    <h3>Authors</h3>
    <div class="list-search-bar">
        <form action="/fics/authors/" accept-charset="UTF-8">
            <div class="search">
                <input class="search" name="search" value="{{ author_search }}" type="text" required placeholder="Search Authors" />
                <input type="submit" class="search-button" value="" />
            </div>
        </form>
    </div>
    {# Display search index. #}
    <table class="list-table">
        <thead>
            <tr>
                <td><strong><a href="{{ nameSortUrl }}">Name</a></strong>{% if sortParam == "name" %}{{ block('sortArrow') }}{% endif %}</td>
                <td><strong><a href="{{ countSortUrl }}">Number of Stories</a></strong>{% if sortParam == "count" %}{{ block('sortArrow') }}{% endif %}</td>
            </tr>
        </thead>
        <tbody>
            {% if authors|length > 0 %}
                {% for author in authors %}
                    <tr>
                        <td><a href="/user/{{ author.UserId }}/fics/">{{ author.DisplayName }}</a></td>
                        <td>{{ author.StoryCount }}</td>
                    </tr>
                {% endfor %}
            {% else %}
                <tr>
                    <td>No Authors Found</td>
                    <td></td>
                </tr>
            {% endif %}
        </tbody>
    </table>
    <div class="Clear">&nbsp;</div>
    <div class="iterator">
        {% autoescape false %}{{ iterator }}{% endautoescape %}
    </div>
{% endblock %}
