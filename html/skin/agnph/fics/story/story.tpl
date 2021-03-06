{% extends 'fics/base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/fics/story/story-style.css')|raw }}
    {{ inline_css_asset('/comments-style.css')|raw }}
{% endblock %}

{% use 'fics/storyblock.tpl' %}
{% use 'fics/reviewblock.tpl' %}

{% block scripts %}
    {{ parent() }}
    <script src="{{ asset('/scripts/tinymce.min.js') }}"></script>
    <script src="{{ asset('/scripts/tinymce-spoiler-plugin.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            {# TODO: Fix javascript #}
            {{ block('reviewready') }}
        });
        {{ block('reviewMCESetup') }}
    </script>
{% endblock %}

{% block content %}
    {{ block('banner') }}
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
                    <span class="chap-word-count">({{ chapter.WordCount }}&nbsp;words)</span>
                    {% if chapter.NumReviews > 0 %}
                        {% for star in chapter.stars %}
                            {% if star == "half" %}
                                <img src='/images/starhalf.gif' />
                            {% elseif star == "full" %}
                                <img src='/images/star.gif' />
                            {% endif %}
                        {% endfor %}
                        <span class="reviews">[<a href="/fics/story/{{ story.StoryId }}/{{ loop.index }}/?reviews#reviews">Reviews:&nbsp;{{ chapter.NumReviews }}</a>]</span>
                    {% endif %}
                </p>
                <p class="chapterlistnotes">{% autoescape false %}{{ chapter.ChapterNotes }}{% endautoescape %}</p>
            </li>
        {% endfor %}
    </ol>
    {{ block('reviewblock') }}
{% endblock %}
