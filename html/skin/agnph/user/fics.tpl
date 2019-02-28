{% extends "user/skin-base.tpl" %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/fics/style.css')|raw }}
    <style>
        .story-list {
            padding: 0px;
            list-style: none;
        }
        .story-list > li {
        }
    </style>
{% endblock %}

{% block sidebar %}
    {% if user and adminLinks|length > 0 %}
        {{ parent() }}
    {% endif %}
{% endblock %}
{% block sidebar_actions %}
    <ul>
        {{ block('admin_link_block') }}
    </ul>
{% endblock %}

{% use "fics/storyblock.tpl" %}

{% block usercontent %}
    <div class="infoblock">
        <h3>Fics Statistics</h3>
        <ul class="basic-info">
            <li><span class="basic-info-label">Stories Uploaded:</span><span>{{ profile.user.numStoriesUploaded }}</span></li>
            <li><span class="basic-info-label">Reviews Posted:</span><span>{{ profile.user.numReviewsPosted }}</span></li>
            {% if showFavorites %}
                <li><span class="basic-info-label">Num Favorites:</span><span>{{ profile.user.numFavorites }}</span></li>
            {% endif %}
        </ul>
    </div>
    {% if profile.user.stories|length > 0 %}
        <div class="infoblock">
            <h3><a href="/fics/browse/?search=author%3A{{ profile.user.underscore_name|url_encode }}">Recent Stories</a></h3>
            <ul class="story-list">
                {% for story in profile.user.stories %}
                    <li>
                        {{ block('storyblock') }}
                    </li>
                {% endfor %}
            </ul>
            <div class="Clear">&nbsp;</div>
            <a href="/fics/browse/?search=author%3A{{ profile.user.underscore_name|url_encode }}">Show all</a>
        </div>
    {% endif %}
    {% if showFavorites and profile.user.favorites|length > 0 %}
        <div class="infoblock">
            <h3><a href="/fics/browse/?search=fav%3A{{ profile.user.underscore_name|url_encode }}">Favorited Stories</a></h3>
            <ul class="story-list">
                {% for story in profile.user.favorites %}
                    <li>
                        {{ block('storyblock') }}
                    </li>
                {% endfor %}
            </ul>
            <div class="Clear">&nbsp;</div>
            <a href="/fics/browse/?search=fav%3A{{ profile.user.underscore_name|url_encode }}">Show all</a>
        </div>
    {% endif %}
{% endblock %}
