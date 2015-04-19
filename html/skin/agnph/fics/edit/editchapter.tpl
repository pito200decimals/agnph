{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/edit-style.css" />
{% endblock %}

{% use 'fics/storyblock.tpl' %}
{% use 'fics/edit/editchapterblock.tpl' %}

{% block scripts %}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <script type="text/javascript">
        {{ block('chapterMCESetup') }}
    </script>
{% endblock %}


{% block ficscontent %}
    <a href="{{ backlink }}">Back</a>
    {% if create %}
        <h3>Create Chapter</h3>
    {% else %}
        <h3>Edit Chapter</h3>
    {% endif %}
    <div>
        {% if errmsg and errmsg|length > 0 %}
            <div class="errormsg">
                Error: {{ errmsg }}
            </div>
        {% endif %}
        <form action="" method="POST">
            <input type="hidden" name="sid" value="{{ storyid }}" />
            {{ block('editchapter') }}
            <input type="submit" name="save" value="Save Changes" />
        </form>
    </div>
{% endblock %}
