{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <style>
        .story-list {
            padding: 0px;
            list-style: none;
        }
        .story-list > li {
        }
    </style>
{% endblock %}

{% block scripts %}
{% endblock %}

{% block sidebar %}
    <h4>Actions</h4>
    <ul>
        {% for link in adminLinks %}
            <li>
                <form id="{{ link.formId }}-form" action="/user/{{ profile.user.UserId }}/admin/" method="POST" accept-encoding="UTF-8" hidden>
                    {% for action in link.actions %}
                        <input type="hidden" name="action[]" value="{{ action }}" />
                    {% endfor %}
                </form>
                <a href="/user/{{ profile.user.UserId }}/admin/" onclick="document.getElementById('{{ link.formId }}-form').submit();return false;">
                    {% autoescape false %}
                        {{ link.text|replace({' ': '&nbsp;'})  }}
                    {% endautoescape %}
                </a>
            </li>
        {% endfor %}
    </ul>
{% endblock %}

{% use "fics/storyblock.tpl" %}

{% block usercontent %}
    <div class="infoblock">
        <h3>Fics Statistics</h3>
        <ul id="basic-info">
            <li><span class="basic-info-label">Stories Uploaded:</span><span>{{ profile.user.numStoriesUploaded }}</span></li>
            <li><span class="basic-info-label">Reviews Posted:</span><span>{{ profile.user.numReviewsPosted }}</span></li>
            {% if showFavorites %}
                <li><span class="basic-info-label">Num Favorites:</span><span>{{ profile.user.numFavorites }}</span></li>
            {% endif %}
        </ul>
    </div>
    {% if profile.user.stories|length > 0 %}
        <div class="infoblock">
            <h3>Recent Stories</h3>
            <ul class="story-list">
                {% for story in profile.user.stories %}
                    <li>
                        {{ block('storyblock') }}
                    </li>
                {% endfor %}
            </ul>
            <div class="Clear">&nbsp;</div>
            <a href="/fics/search/?search={{ profile.user.DisplayName|url_encode }}">Show all</a>
        </div>
    {% endif %}
    {% if showFavorites and profile.user.favorites|length > 0 %}
        <div class="infoblock">
            <h3>Favorited Stories</h3>
            <ul class="story-list">
                {% for story in profile.user.favorites %}
                    <li>
                        {{ block('storyblock') }}
                    </li>
                {% endfor %}
            </ul>
            <div class="Clear">&nbsp;</div>
            <a href="/fics/search/?search=fav%3A{{ profile.user.DisplayName|url_encode }}">Show all</a>
        </div>
    {% endif %}
{% endblock %}
