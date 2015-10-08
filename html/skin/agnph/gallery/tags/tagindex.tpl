{% extends 'gallery/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
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
    <form method="GET" accept-charset="UTF-8">
        <input type="hidden" name="sort" value="{{ sortParam }}" />
        <input type="hidden" name="order" value="{{ orderParam }}" />
        <label>Search for Tags:</label><input class="search" name="search" type="text" value="{{ search }}" required/>
    </form>
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
                        <td class="tag"><strong><a class="{{ tag.typeClass }}" href="/gallery/post/?search={{ tag.quotedName|url_encode }}">{{ tag.displayName }}</a></strong></td>
                        <td>{{ tag.typeName }}{% if tag.EditLocked %} (locked){% endif %}</td>
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
