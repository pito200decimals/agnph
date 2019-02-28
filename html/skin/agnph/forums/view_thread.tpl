{% extends 'forums/skin-base.tpl' %}

{% block styles %}
    {{ parent() }}
    {# included in parent #}
    {#{{ inline_css_asset('/comments-style.css')|raw }}#}
    <style>
        .signature {
            max-height: 200px;
            overflow-y: hidden;
        }
    </style>
{% endblock %}

{% use 'includes/comment-block.tpl' %}

{% block content %}
    {{ block('breadcrumb_bar') }}
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
        {% if user %}
            <ul class="forums-actionbar">
                {% if canReply %}
                    <li><a href="/forums/compose/?action=reply&id={{ thread.ThreadId }}">Reply</a></li>
                {% endif %}
            </ul>
        {% endif %}
        <div class="iterator">
            {% autoescape false %}{{ iterator }}{% endautoescape %}
        </div>
    {% else %}
        Thread not found
    {% endif %}
{% endblock %}
