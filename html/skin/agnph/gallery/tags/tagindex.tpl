{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/tagindex-style.css" />
{% endblock %}

{% block gallerycontent %}
    <div class="mainpanel">
        <h3>Tags</h3>
        {% if tags|length > 0 %}
            {# Display search index. #}
            <table class="tagtable">
                <tr>
                    <td><strong>Name</strong></td>
                    <td><strong>Type</strong></td>
                </tr>
                {% for tag in tags %}
                    <tr>
                        <td><a class="{{ tag.typeClass }}" href="/gallery/post/?search={{ tag.Name }}">{{ tag.Name }}</a></td>
                        <td>{{ tag.typeName }}</td>
                    </tr>
                {% endfor %}
            </table>
            <div class="Clear">&nbsp;</div>
            <div class="indexIterator">
                {% autoescape false %}
                {{ iterator }}
                {% endautoescape %}
            </div>
        {% else %}
            {# No posts here. #}
            No tags found.
        {% endif %}
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
