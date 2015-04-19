{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/story/story-style.css" />
{% endblock %}

{% use 'fics/storyblock.tpl' %}

{% block ficscontent %}
    {{ block('storyblock') }}
    {% if story.StoryNotes|length > 0 %}
        <div class="storynotes">
            <p><strong>Story Notes</strong></p>
            <p>
                {% autoescape false %}{{ story.StoryNotes }}{% endautoescape %}
            </p>
        </div>
    {% endif %}
    <ol>
        {% for chapter in chapters %}
            <li>
                <p class="chaptertitle">
                    <a href="/fics/story/{{ story.StoryId }}/{{ loop.index }}/">
                        <strong>{{ chapter.Title }}</strong>
                    </a>
                </p>
                <p class="chapternotes">{% autoescape false %}{{ chapter.ChapterNotes }}{% endautoescape %}</p>
            </li>
        {% endfor %}
    </ol>
{% endblock %}
