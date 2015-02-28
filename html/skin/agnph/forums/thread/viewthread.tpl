{% extends 'base.tpl' %}

{% block content %}
    {% if thread %}
        <div style="margin: 10px;">
            <h3>Thread Title: {{ thread.title }}</h3>
        </div>
        {% if thread.posts %}
            {% for post in thread.posts %}
                {% include 'forums/thread/postblock.tpl' %}
            {% endfor %}
        {% endif %}
    {% elseif content %}
        {{ content }}
    {% else %}
        No content here.
    {% endif %}
{% endblock %}
