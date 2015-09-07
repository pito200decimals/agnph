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
    {% if thread.posts|length > 0 %}
        {% if user %}
            <ul class="forums-actionbar">
                {% if canReply %}
                    <li><a href="/forums/compose/?action=reply&id={{ thread.ThreadId }}">Reply</a></li>
                {% endif %}
            </ul>
        {% endif %}
        <div class="Clear">&nbsp;</div>
        <ul class="comment-list">
            {% for comment in thread.posts %}
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
