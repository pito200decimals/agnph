{% extends 'fics/base.tpl' %}

{% set enable_right_panel=true %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
    {% endif %}
    {% if user %}
        <script src="{{ asset('/scripts/irc.js') }}"></script>
    {% endif %}
{% endblock %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/irc-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/user-activity-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/comments-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/index-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/fics/index-style.css') }}" />
{% endblock %}

{% use 'includes/irc-block.tpl' %}
{% use 'includes/user-activity-block.tpl' %}

{% macro storyitem(story) %}
    {% import 'fics/stars.tpl' as stars %}
    <div class="story-item">
        {% if story.Featured=="F" %}
            <img class="ribbon" src="/images/blueribbon.gif" title="Featured Story" />
        {% elseif story.Featured=="G" %}
            <img class="ribbon" src="/images/goldribbon.gif" title="Gold Ribbon" />
        {% elseif story.Featured=="S" %}
            <img class="ribbon" src="/images/silverribbon.gif" title="Silver Ribbon" />
        {% elseif story.Featured=="Z" %}
            <img class="ribbon" src="/images/bronzeribbon.gif" title="Bronze Ribbon" />
        {% elseif story.Featured=="f" %}
            <img class="ribbon" src="/images/redribbon.gif" title="Featured Story" />
        {% elseif story.Featured=="g" %}
            <img class="ribbon" src="/images/goldribbon_old.gif" title="Gold Ribbon" />
        {% elseif story.Featured=="s" %}
            <img class="ribbon" src="/images/silverribbon_old.gif" title="Silver Ribbon" />
        {% elseif story.Featured=="z" %}
            <img class="ribbon" src="/images/bronzeribbon_old.gif" title="Bronze Ribbon" />
        {% endif %}
        <div class="title">
            <a href="/fics/story/{{ story.StoryId }}/">{{ story.Title }}</a> by <a href="/user/{{ story.author.UserId }}/fics/">{{ story.author.DisplayName }}</a>
            <span class="stars">{{ stars.stars(story) }}</span>
        </div>
        <div class="summary">{% autoescape false %}{{ story.shortSummary }}{% endautoescape %}</div>
        <div class="rating"><strong>Rating:</strong> {{ story.rating }}</div>
    </div>
{% endmacro %}

{% block left_panel %}
    {% if events %}
        <div class="block">
            <div class="header">Events</div>
            <div class="content">{% autoescape false %}{{ events }}{% endautoescape %}</div>
        </div>
    {% endif %}
    {{ parent() }}
    <div class="desktop-only">
        {{ block('user_activity_block') }}
    </div>
{% endblock %}

{% block right_panel %}
    {% import _self as self %}
    {% if featured|length > 0 %}
        <div class="block">
            <div class="header">Featured Stories</div>
            <div class="content">
                <ul id="feature-list">
                    {% for story in featured %}
                        <li>{{ self.storyitem(story) }}</li>
                    {% endfor %}
                </ul>
            </div>
        </div>
    {% endif %}
    {% if recent_stories|length > 0 %}
        <div class="block">
            <div class="header">Recent Stories</div>
            <div class="content">
                {% for story in recent_stories %}
                    {{ self.storyitem(story) }}
                {% endfor %}
            </div>
        </div>
    {% endif %}
    {% if random_stories|length > 0 %}
        <div class="block">
            <div class="header">Random</div>
            <div class="content">
                {% for story in random_stories %}
                    {{ self.storyitem(story) }}
                {% endfor %}
            </div>
        </div>
    {% endif %}
    {% if user %}
        <div class="desktop-only">
            {{ block('irc_block') }}
        </div>
    {% endif %}
{% endblock %}

{% block content %}
    {{ block('banner') }}
    {% if welcome_message %}
        <div class="block">
            <div class="header">Welcome</div>
            <div class="content">{% autoescape false %}{{ welcome_message }}{% endautoescape %}</div>
        </div>
    {% endif %}
    {% for post in news %}
        <div class="block{% if not post.mobile %} desktop-only{% endif %}">
            <div class="header">
                <a href="/forums/thread/{{ post.PostId }}/">{{ post.Title }}</a>
                <div class="tagline">
                    Posted {{ post.date }} by <a href="/user/{{ post.user.UserId }}/">{{ post.user.DisplayName }}</a>
                </div>
                <div class="Clear">&nbsp;</div>
            </div>
            <div class="content">
                {% autoescape false %}{{ post.Text }}{% endautoescape %}
            </div>
        </div>
    {% endfor %}
{% endblock %}
