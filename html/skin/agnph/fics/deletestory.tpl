{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <style>
        .delete {
            margin: 10px;
        }
    </style>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}

{% use 'fics/storyblock.tpl' %}

{% block content %}
    {{ block('storyblock') }}
    <div class="delete">
        Are you sure you want to {{ actionName }}?
        <form method="POST">
            {% if chapterHash and chapterIndex %}
                <input type="hidden" name="id" value="{{ chapterHash }}" />
                <input type="hidden" name="index" value="{{ chapterIndex }}" />
            {% endif %}
            <input name="confirm" type="submit" value="{{ buttonText }}" />
        </form>
    </div>
{% endblock %}
