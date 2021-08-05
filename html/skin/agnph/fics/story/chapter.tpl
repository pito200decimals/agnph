{% extends 'fics/base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/fics/story/story-style.css')|raw }}
    {{ inline_css_asset('/fics/story/chapter-style.css')|raw }}
    {{ inline_css_asset('/comments-style.css')|raw }}
{% endblock %}

{% use 'fics/reviewblock.tpl' %}

{% block scripts %}
    {{ parent() }}
    <script src="{{ asset('/scripts/tinymce.min.js') }}"></script>
    <script src="{{ asset('/scripts/tinymce-spoiler-plugin.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            {{ block('reviewready') }}
        });
        {{ block('reviewMCESetup') }}
        SetUpFontSizes("fics-zoom", ".fics-font-size-switcher", ".fics-text-content");
    </script>
{% endblock %}

{% block prevnext %}
    <div class="chapter-iterator">&nbsp;
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

{% block fics_font_size %}
    <span class="font-size-switcher-container fics-font-size-switcher" hidden>
        Chapter Text Size:
        <select>
            <option>80%</option>
            <option>90%</option>
            <option>100%</option>
            <option>120%</option>
            <option>150%</option>
        </select>
    </span>
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div class="Clear">&nbsp;</div>
    <h3><a href="/fics/story/{{ story.StoryId }}/">{{ story.Title }}</a> by <a href="/user/{{ story.author.UserId }}/fics/">{{ story.author.DisplayName }}</a></h3>
    {{ block('fics_font_size') }}
    <div class="Clear">&nbsp;</div>
    {{ block('prevnext') }}
    <div class="fics-text-content">
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
        <h3>{{ chapter.Title }}{% if chapter.author.DisplayName != story.author.DisplayName %}{{ " " }}by <a href="/user/{{ chapter.author.UserId }}/fics/">{{ chapter.author.DisplayName }}</a>{% endif %}</h3>
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
    </div>
    {{ block('prevnext') }}
    {{ block('reviewblock') }}
{% endblock %}
