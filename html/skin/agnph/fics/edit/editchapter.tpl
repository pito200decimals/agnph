{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/edit-style.css" />
{% endblock %}

{% use 'fics/storyblock.tpl' %}
{% use 'fics/edit/editchapterblock.tpl' %}

{% block scripts %}
    {{ parent() }}
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <script type="text/javascript">
        {{ block('chapterMCESetup') }}
    </script>
{% endblock %}


{% block content %}
    <a href="{{ backlink }}">Back</a>
    {% if create %}
        <h3>Create Chapter</h3>
    {% else %}
        <h3>Edit Chapter</h3>
    {% endif %}
    {{ block('banner') }}
    <form method="POST" accept-charset="UTF-8">
        <input type="hidden" name="sid" value="{{ storyid }}" />
        {{ block('editchapter') }}
        <input type="submit" name="save" value="Save Changes" />
    </form>
{% endblock %}
