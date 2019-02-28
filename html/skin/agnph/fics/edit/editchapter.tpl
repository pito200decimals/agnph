{% extends 'fics/base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/fics/edit-style.css')|raw }}
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
    <form method="POST" enctype="multipart/form-data" accept-charset="UTF-8">
        <input type="hidden" name="sid" value="{{ storyid }}" />
        {{ block('editchapter') }}
        <input type="submit" name="save" value="Save Changes" />
    </form>
{% endblock %}
