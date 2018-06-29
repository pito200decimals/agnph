{% extends "base.tpl" %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
    {% endif %}
    <script src="{{ asset('/scripts/irc.js') }}"></script>
{% endblock %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/irc-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/user-activity-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/comments-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/index-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/no-left-panel-mobile-style.css') }}" />
    {% if not site_nav_in_tray %}
        <link rel="stylesheet" type="text/css" href="{{ asset('/no-left-menu-style.css') }}" />
    {% endif %}
{% endblock %}
                                        
{% use 'includes/irc-block.tpl' %}
{% use 'includes/user-activity-block.tpl' %}

{% block left_panel %}
    {% if events %}
        <div class="block">
            <div class="header">Events</div>
            <div class="content">{% autoescape false %}{{ events }}{% endautoescape %}</div>
        </div>
    {% endif %}
    {{ parent() }}
    {{ block('user_activity_block') }}
{% endblock %}

{% block right_panel %}
    <div class="desktop-only">
        {{ block('irc_block') }}
    </div>
{% endblock %}

{% block content %}
    {{ block('banner') }}
    {% if welcome_message %}
        <div class="block">
            <div class="header">Welcome</div>
            <div class="content">{% autoescape false %}{{ welcome_message }}{% endautoescape %}</div>
        </div>
    {% endif %}
    {% if news|length > 0 %}
        <h3>Recent News</h3>
        {% for post in news %}
            <div class="block">
                <div class="header">
                    {{ post.section }} - <a href="/forums/thread/{{ post.PostId }}/">{{ post.Title }}</a>
                    <div class="tagline">
                        Posted {{ post.date }} by <a href="/user/{{ post.user.UserId }}/">{{ post.user.DisplayName }}</a>
                    </div>
                    <div class="Clear">&nbsp;</div>
                </div>
                <div class="content">
                    {% autoescape false %}{{ post.Text }}{% endautoescape %}
                </div>
                <div class="footer">
                    <a href="/forums/thread/{{ post.PostId }}/">Comments ({{ post.Replies }})</a>
                </div>
            </div>
        {% endfor %}
    {% endif %}
{% endblock %}
