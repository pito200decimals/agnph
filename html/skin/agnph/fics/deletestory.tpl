{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/delete-style.css" />
{% endblock %}

{% block scripts %}
{% endblock %}

{% use 'fics/storyblock.tpl' %}

{% block content %}
    {{ block('storyblock') }}
    <div class="delete">
        Are you sure you want to {{ button|lower }}?
        <form action="" method="POST">
            <input name="confirm" type="submit" value="{{ button }}" />
        </form>
    </div>
{% endblock %}
