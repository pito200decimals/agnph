{% extends 'forums/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/forums/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/comments-style.css" />
    <style>
        .signature {
            max-height: 200px;
            overflow-y: hidden;
        }
    </style>
{% endblock %}

{% use 'includes/comment-block.tpl' %}

{% block content %}
    <h3>{{ thread.Title }}</h3>
    {{ block('banner') }}
    <a href="/forums/compose/?action=reply&id={{ thread.ThreadId }}">Reply (TODO)</a>
    {% if thread.posts|length > 0 %}
        <ul class="comment-list">
            {% for comment in thread.posts %}
                {% if comment.unread %}
                    [UNREAD]
                {% endif %}
                {{ block('comment') }}
            {% endfor %}
        </ul>
        <div class="iterator">
            {% autoescape false %}{{ iterator }}{% endautoescape %}
        </div>
    {% else %}
        Thread not found
    {% endif %}
{% endblock %}
