{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/story/story-style.css" />
{% endblock %}

{% use 'fics/storyblock.tpl' %}

{% block ficscontent %}
    {{ block('storyblock') }}
{% endblock %}
