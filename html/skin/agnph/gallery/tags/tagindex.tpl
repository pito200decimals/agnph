{% extends 'gallery/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
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
    {% if tags|length > 0 %}
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
                {% for tag in tags %}
                    <tr>
                        <td><strong><a class="{{ tag.typeClass }}" href="/gallery/post/?search={{ tag.quotedName|url_encode }}">{{ tag.displayName }}</a></strong></td>
                        <td>{{ tag.typeName }}{% if tag.EditLocked %} (locked){% endif %}</td>
                        <td>{{ tag.tagCounts }}</td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>
        <div class="Clear">&nbsp;</div>
        <div class="iterator">
            {% autoescape false %}{{ iterator }}{% endautoescape %}
        </div>
    {% else %}
        {# No tags here. #}
        <table class="list-table">
            <thead>
                <tr>
                    <td><strong><a href="{{ nameSortUrl }}">Name</a></strong>{% if sortParam == "name" %}{{ block('sortArrow') }}{% endif %}</td>
                    <td><strong><a href="{{ typeSortUrl }}">Type</a></strong>{% if sortParam == "type" %}{{ block('sortArrow') }}{% endif %}</td>
                    <td><strong><a href="{{ countSortUrl }}">Count</a></strong>{% if sortParam == "count" %}{{ block('sortArrow') }}{% endif %}</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3">No Tags found</td>
                </tr>
            </tbody>
        </table>
    {% endif %}
{% endblock %}
