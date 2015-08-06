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

{# TODO: Sidebar for user fics actions. #}
{% block sidebar %}
{% endblock %}

{% use "fics/storyblock.tpl" %}

{% block usercontent %}
    <div class="infoblock">
        <h3>Fics Statistics</h3>
        <ul id="basic-info">
            <li><span class="basic-info-label">Stories Uploaded:</span><span>{{ profile.user.numStoriesUploaded }}</span></li>
            <li><span class="basic-info-label">Reviews Posted:</span><span>{{ profile.user.numReviewsPosted }}</span></li>
            <li><span class="basic-info-label">Num Favorites:</span><span>{{ profile.user.numFavorites }}</span></li>
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
    {% if profile.user.favorites|length > 0 %}
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
