{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/list-style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
{% endblock %}

{% block content %}
    <div class="mainpanel">
        <h3>Tags</h3>
        <form action="/gallery/tags/" accept-charset="UTF-8">
            <label>Search for Tags:</label><input class="search" name="prefix" type="text" value="{{ searchPrefix }}" required/>
        </form>
        {% if tags|length > 0 %}
            {# Display tag index. #}
            <table class="list-table">
                <thead>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td><strong>Type</strong></td>
                        <td><strong>Count</strong></td>
                    </tr>
                </thead>
                <tbody>
                    {% for tag in tags %}
                        <tr>
                            <td><strong><a class="{{ tag.typeClass }}" href="/gallery/post/?search={{ tag.Name }}">{{ tag.Name }}</a></strong></td>
                            <td>{{ tag.typeName }}{% if tag.EditLocked %} (locked){% endif %}</td>
                            <td>{{ tag.tagCounts }}</td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
            <div class="Clear">&nbsp;</div>
            <div class="iterator">
                {% autoescape false %}
                {{ iterator }}
                {% endautoescape %}
            </div>
        {% else %}
            {# No tags here. #}
            No tags found.
        {% endif %}
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
