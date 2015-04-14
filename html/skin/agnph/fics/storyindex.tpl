{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/storyindex-style.css" />
{% endblock %}

{% use 'fics/storyblock.tpl' %}

{% block ficscontent %}
    <div style="padding: 5px;">
    {% for story in stories %}
        {{ block('storyblock') }}
    {% endfor %}
    </div>
{% endblock %}
