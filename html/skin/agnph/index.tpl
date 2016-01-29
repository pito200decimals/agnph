{% extends "base.tpl" %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
    {% endif %}
{% endblock %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/index-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/comments-style.css') }}" />
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div class="index-table">
        <div class="right-column">
            <div class="column-contents">
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
                {% if random_stories %}
                    <div class="block">
                        <div class="header">Random</div>
                        <div class="content">
                            {% for story in random_stories %}
                                {{ self.storyitem(story) }}
                            {% endfor %}
                        </div>
                    </div>
                {% endif %}
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
