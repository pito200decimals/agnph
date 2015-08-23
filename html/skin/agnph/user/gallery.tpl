{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/postindex-style.css" />
{% endblock %}

{% block scripts %}
{% endblock %}

{% block sidebar %}
    {% if user %}
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
    {% endif %}
{% endblock %}

{% block usercontent %}
    <div class="infoblock">
        <h3>Gallery Statistics</h3>
        <ul id="basic-info">
            <li><span class="basic-info-label">Posts Uploaded:</span><span>{{ profile.user.numGalleryPostsUploaded }} {{ profile.user.galleryPostsUploadedDetail }}</span></li>
            <li><span class="basic-info-label">Upload Limit:</span><span><span title="Base upload limit" style="cursor: help;">{{ profile.user.numBaseUploadLimit }}</span> + (<span title="Number of approved uploads" style="cursor: help;">{{ profile.user.numGoodUploads }}</span> / 10) - (<span title="Number of deleted uploads" style="cursor: help;">{{ profile.user.numBadUploads }}</span> / 4) = <span title="User upload limit" style="cursor: help; font-weight: bold;">{{ profile.user.numUploadLimit }}</span></span></li>
            <li><span class="basic-info-label">Posts Flagged:</span><span>{{ profile.user.numGalleryPostsFlagged }}</span></li>
            {% if showFavorites %}
                <li><span class="basic-info-label">Posts Favorited:</span><span>{{ profile.user.numGalleryPostsFavorited }}</span></li>
            {% endif %}
            <li><span class="basic-info-label">Tag Edits:</span><span>{{ profile.user.numGalleryTagEdits }}</span></li>
            <li><span class="basic-info-label">Post Comments:</span><span>{{ profile.user.numGalleryPostComments }}</span></li>
        </ul>
    </div>
    {% if profile.user.uploads|length > 0 %}
        <div class="infoblock">
            <h3>Recent Uploads</h3>
            <ul class="post-list">
                {% for post in profile.user.uploads %}
                    <li class="dragitem">
                        <a class="postlink" href="/gallery/post/show/{{ post.PostId }}/">
                            <div class="post-tile">
                                {# TODO: Deleted thumbnail instead of preview? #}
                                <img class="post-preview-img {{ post.outlineClass }}" src="{{ post.thumbnail }}" />
                                <div class="post-label">
                                    {% autoescape false %}
                                    {{ post.favHtml }}{{ post.commentsHtml }}{{ post.ratingHtml }}
                                    {% endautoescape %}
                                </div>
                            </div>
                        </a>
                    </li>
                {% endfor %}
            </ul>
            <div class="Clear">&nbsp;</div>
            <a href="/gallery/post/?search=user%3A{{ profile.user.DisplayName|url_encode }}">Show all</a>
        </div>
    {% endif %}
    {% if showFavorites and profile.user.favorites|length > 0 %}
        <div class="infoblock">
            <h3>User Favorites</h3>
            <ul class="post-list">
                {% for post in profile.user.favorites %}
                    <li class="dragitem">
                        <a class="postlink" href="/gallery/post/show/{{ post.PostId }}/">
                            <div class="post-tile">
                                {# TODO: Deleted thumbnail instead of preview? #}
                                <img class="post-preview-img {{ post.outlineClass }}" src="{{ post.thumbnail }}" />
                                <div class="post-label">
                                    {% autoescape false %}
                                    {{ post.favHtml }}{{ post.commentsHtml }}{{ post.ratingHtml }}
                                    {% endautoescape %}
                                </div>
                            </div>
                        </a>
                    </li>
                {% endfor %}
            </ul>
            <div class="Clear">&nbsp;</div>
            <a href="/gallery/post/?search=fav%3A{{ profile.user.DisplayName|url_encode }}">Show all</a>
        </div>
    {% endif %}
{% endblock %}
