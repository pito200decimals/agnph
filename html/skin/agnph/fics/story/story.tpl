{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/story/story-style.css" />
{% endblock %}

{% use 'fics/storyblock.tpl' %}
{% use 'fics/reviewblock.tpl' %}

{% block scripts %}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            {{ block('reviewready') }}
        });
    </script>
{% endblock %}

{% block ficscontent %}
    {{ block('storyblock') }}
    {% if story.StoryNotes|length > 0 %}
        <div class="notesbox">
            <p><strong>Story Notes:</strong></p>
            <p id="storynotes">
                {% autoescape false %}{{ story.StoryNotes }}{% endautoescape %}
            </p>
        </div>
    {% endif %}
    <ol>
        {% for chapter in chapters %}
            <li>
                <p class="chapterlisttitle">
                    <a href="/fics/story/{{ story.StoryId }}/{{ loop.index }}/">
                        <strong>{{ chapter.Title }}</strong>
                    </a>
                    {% autoescape false %}{{ chapter.stars }}{% endautoescape %}
                </p>
                <p class="chapterlistnotes">{% autoescape false %}{{ chapter.ChapterNotes }}{% endautoescape %}</p>
            </li>
        {% endfor %}
    </ol>
    {{ block('reviewblock') }}
{% endblock %}
