{% extends 'fics/base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/list-style.css')|raw }}
    <style>
        .tag a,.tag a:hover {
            text-decoration: none;
            font-weight: bold;
        }
    </style>
{% endblock %}

{% block sortArrow %}
    {% if orderParam == "desc" %}
        ▼
    {% else %}
        ▲
    {% endif %}
{% endblock %}

{% block content %}
    <h3>Tags</h3>
    <div class="list-search-bar">
        <form method="GET" accept-charset="UTF-8">
            <input type="hidden" name="sort" value="{{ sortParam }}" />
            <input type="hidden" name="order" value="{{ orderParam }}" />
            <div class="search">
                <input class="search" name="search" value="{{ tag_search }}" type="text" required placeholder="Search Tags" />
                <input type="submit" class="search-button" value="" />
            </div>
        </form>
    </div>
    {# Display tag index. #}
    <table class="list-table">
        <thead>
            <tr>
                <td><strong><a href="{{ nameSortUrl }}">Name</a></strong>{% if sortParam == "name" %}{{ block('sortArrow') }}{% endif %}</td>
                <td><strong><a href="{{ typeSortUrl }}">Type</a></strong>{% if sortParam == "type" %}{{ block('sortArrow') }}{% endif %}</td>
                <td><strong><a href="{{ countSortUrl }}">Count</a></strong>{% if sortParam == "count" %}{{ block('sortArrow') }}{% endif %}</td>
            </tr>
        </thead>
        <tbody>
            {% if tags|length > 0 %}
                {% for tag in tags %}
                    <tr>
                        <td class="tag"><strong><a class="{{ tag.typeClass }}" href="/fics/browse/?search=tag%3A{{ tag.quotedName|url_encode }}">{{ tag.displayName }}</a></strong></td>
                        <td>{{ tag.typeName }}</td>
                        <td>{{ tag.tagCounts }}</td>
                    </tr>
                {% endfor %}
            {% else %}
                <tr>
                    <td></td>
                    <td colspan="2">No Tags found</td>
                </tr>
            {% endif %}
        </tbody>
    </table>
    <div class="Clear">&nbsp;</div>
    <div class="iterator">
        {% autoescape false %}{{ iterator }}{% endautoescape %}
    </div>
{% endblock %}
