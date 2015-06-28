{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/postindex-style.css" />
{% endblock %}

{% block scripts %}
{% endblock %}

{# TODO: Sidebar for user gallery actions. #}
{% block sidebar %}
{% endblock %}

{% block usercontent %}
    <div class="infoblock">
        <h3>Gallery Statistics</h3>
        <ul id="basic-info">
            <li><span class="basic-info-label">Posts Uploaded:</span><span>{{ profile.user.numGalleryPostsUploaded }}</span></li>
            <li><span class="basic-info-label">Upload Limit:</span><span>TODO{{ profile.user.uploadLimit }}</span></li>
            <li><span class="basic-info-label">Posts Flagged:</span><span>{{ profile.user.numGalleryPostsFlagged }}</span></li>
            <li><span class="basic-info-label">Posts Favorited:</span><span>{{ profile.user.numGalleryPostsFavorited }}</span></li>
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
                            <div class="postsquare">
                                <div class="postsquarepreview">
                                    {# TODO: Deleted thumbnail instead of preview #}
                                    <img class="postsquarepreview {{ post.outlineClass }}" src="{{ post.thumbnail }}" />
                                </div>
                                <div class="postlabel">
                                    {% autoescape false %}
                                    {{ post.scoreHtml }}{{ post.favHtml }}{{ post.commentsHtml }}{{ post.ratingHtml }}
                                    {% endautoescape %}
                                </div>
                            </div>
                        </a>
                    </li>
                {% endfor %}
            </ul>
            <div class="Clear">&nbsp;</div>
            <a href="/gallery/post/?search=user%3A{{ profile.user.DisplayName }}">Show all</a>
        </div>
    {% endif %}
    {% if profile.user.favorites|length > 0 %}
        <div class="infoblock">
            <h3>UserFavorites</h3>
            <ul class="post-list">
                {% for post in profile.user.favorites %}
                    <li class="dragitem">
                        <a class="postlink" href="/gallery/post/show/{{ post.PostId }}/">
                            <div class="postsquare">
                                <div class="postsquarepreview">
                                    {# TODO: Deleted thumbnail instead of preview #}
                                    <img class="postsquarepreview {{ post.outlineClass }}" src="{{ post.thumbnail }}" />
                                </div>
                                <div class="postlabel">
                                    {% autoescape false %}
                                    {{ post.scoreHtml }}{{ post.favHtml }}{{ post.commentsHtml }}{{ post.ratingHtml }}
                                    {% endautoescape %}
                                </div>
                            </div>
                        </a>
                    </li>
                {% endfor %}
            </ul>
            <div class="Clear">&nbsp;</div>
            <a href="/gallery/post/?search=fav%3A{{ profile.user.DisplayName }}">Show all</a>
        </div>
    {% endif %}
{% endblock %}
