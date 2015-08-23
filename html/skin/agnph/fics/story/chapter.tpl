{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/story/story-style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/story/chapter-style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/comments-style.css" />
{% endblock %}

{% use 'fics/reviewblock.tpl' %}

{% block scripts %}
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            {{ block('reviewready') }}
        });
        {{ block('reviewMCESetup') }}
    </script>
{% endblock %}

{% block prevnext %}
    <div>&nbsp;
        {% if chapter.ChapterItemOrder > 0 %}
        <div id="prev">
            <a href="/fics/story/{{ story.StoryId }}/{{ chapter.ChapterItemOrder }}/">&lt;&lt;&lt;&nbsp;Previous</a>
        </div>
        {% endif %}
        {% if chapter.ChapterItemOrder < numchapters - 1 %}
        <div id="next">
            <a href="/fics/story/{{ story.StoryId }}/{{ chapter.ChapterItemOrder + 2 }}/">Next&nbsp;&gt;&gt;&gt;</a>
        </div>
        {% endif %}
    </div>
    {# TODO: Add chapter dropdown box? #}
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <h3><a href="/fics/story/{{ story.StoryId }}/">{{ story.Title }}</a> by <a href="/user/{{ story.author.UserId }}/fics/">{{ story.author.DisplayName }}</a></h3>
    {{ block('prevnext') }}
    {% if story.StoryNotes|length > 0 %}
        <div class="notesbox">
            <p><strong>Story Notes:</strong></p>
            <p id="storynotes">
                {% autoescape false %}{{ story.StoryNotes }}{% endautoescape %}
            </p>
        </div>
    {% endif %}
    {% if chapter.ChapterNotes|length > 0 %}
        <div class="notesbox">
            <p><strong>Author's Chapter Notes:</strong></p>
            <p id="chapternotes">
                {% autoescape false %}{{ chapter.ChapterNotes }}{% endautoescape %}
            </p>
        </div>
    {% endif %}
    {% if story.StoryNotes|length > 0 or chapter.ChapterNotes|length > 0 %}
        <hr />
    {% endif %}
    <h3>{{ chapter.Title }}{% if chapter.author.DisplayName != story.author.DisplayName %} by <a href="/user/{{ chapter.author.UserId }}/fics/">{{ chapter.author.DisplayName }}</a>{% endif %}</h3>
    <div class="chaptercontent">
        {% autoescape false %}{{ chapter.text }}{% endautoescape %}
    </div>
    {% if chapter.ChapterEndNotes|length > 0 %}
        <hr />
        <div class="notesbox">
            <p><strong>Chapter End Notes:</strong></p>
            <p id="chapterendnotes">
                {% autoescape false %}{{ chapter.ChapterEndNotes }}{% endautoescape %}
            </p>
        </div>
    {% endif %}
    {{ block('prevnext') }}
    {{ block('reviewblock') }}
{% endblock %}
