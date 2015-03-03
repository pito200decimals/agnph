{% extends 'base.tpl' %}

{% block content %}
    <h3>{{ title }}</h3>
    {% if editorForm %}
        {% if previewBlock %}
            {% set post = previewBlock %}
            {% include 'forums/thread/postblock.tpl' %}
        {% endif %}
        {% autoescape false %}
            {{ editorForm }}
        {% endautoescape %}
        <h3>{{ postsTitle }}</h3>
        {% if posts %}
            {% for post in posts %}
                {% include 'forums/thread/postblock.tpl' %}
            {% endfor %}
        {% endif %}
    {% elseif content %}
        {{ content }}
    {% else %}
        No content here.
    {% endif %}
{% endblock %}
