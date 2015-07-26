{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/tagindex-style.css" />
{% endblock %}

{% block content %}
    <div class="mainpanel">
        <h3>Tags</h3>
        <form action="/gallery/tags/" accept-charset="UTF-8">
            <label>Search for Tags:</label><input class="search" name="prefix" type="text" value="{{ searchPrefix }}" required/>
        </form>
        {% if tags|length > 0 %}
            {# Display tag index. #}
            <table class="tagtable">
                <thead>
                    <tr>
                        <td><div><strong>Name</strong></div></td>
                        <td><div><strong>Type</strong></div></td>
                        <td><div><strong>Count</strong></div></td>
                    </tr>
                </thead>
                <tbody>
                    {% for tag in tags %}
                        <tr>
                            <td><div><a class="{{ tag.typeClass }}" href="/gallery/post/?search={{ tag.Name }}">{{ tag.Name }}</a></div></td>
                            <td><div>{{ tag.typeName }}{% if tag.EditLocked %} (locked){% endif %}</div></td>
                            <td><div>{{ tag.tagCounts }}</div></td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
            <div class="Clear">&nbsp;</div>
            <div class="indexIterator">
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
