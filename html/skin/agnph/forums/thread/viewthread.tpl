{% extends 'base.tpl' %}

{% block content %}
    {% if thread %}
        <div style="margin: 10px;">
            <h3>Thread Title: {{ thread.Title }}</h3>
            <small>Thread posted by {{ thread.creator.DisplayName }}</small>
        </div>
        {% if thread.Posts %}
            {% autoescape false %}
                <div style="margin:15px;">
                    Pages: {{ page_iterator }}
                </div>
            {% endautoescape %}
            {% for post in thread.Posts %}
                {% if post.new %}<a name="new" />{% endif %}
                {% include 'forums/thread/postblock.tpl' %}
            {% endfor %}
            {% autoescape false %}
                <div style="margin:15px;">
                    Pages: {{ page_iterator }}
                </div>
            {% endautoescape %}
        {% endif %}
    {% elseif content %}
        {{ content }}
    {% else %}
        No content here.
    {% endif %}
{% endblock %}
