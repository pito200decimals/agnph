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
{% endblock %}

{% use 'includes/irc-block.tpl' %}
{% use 'includes/user-activity-block.tpl' %}

{% block content %}
    {{ block('banner') }}
    <div class="index-table">
        <div class="right-column">
            <div class="column-contents">
                <div class="desktop-only">
                    {{ block('irc_block') }}
                </div>
                <div class="desktop-only">
                    {{ block('user_activity_block') }}
                </div>
            </div>
        </div>

        <div class="center-column">
            <div class="column-contents">
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
            </div>
        </div>

        <div class="left-column">
            <div class="column-contents">
                {% if events %}
                    <div class="block">
                        <div class="header">Events</div>
                        <div class="content">{% autoescape false %}{{ events }}{% endautoescape %}</div>
                    </div>
                {% endif %}
            </div>
        </div>
    </div>
{% endblock %}
