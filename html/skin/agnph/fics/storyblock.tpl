{% block storyblock %}
<div class="storyblock">
    <div class="storyblockcontainer">
        <div class="storyblockheader">
            {% if story.Featured=="F" %}
                <img class="ribbon" src="/images/blueribbon.gif" />
            {% elseif story.Featured=="G" %}
                <img class="ribbon" src="/images/goldribbon.gif" />
            {% elseif story.Featured=="S" %}
                <img class="ribbon" src="/images/silverribbon.gif" />
            {% elseif story.Featured=="Z" %}
                <img class="ribbon" src="/images/bronzeribbon.gif" />
            {% elseif story.Featured=="f" %}
                <img class="ribbon" src="/images/redribbon.gif" />
            {% elseif story.Featured=="g" %}
                <img class="ribbon" src="/images/goldribbon_old.gif" />
            {% elseif story.Featured=="s" %}
                <img class="ribbon" src="/images/silverribbon_old.gif" />
            {% elseif story.Featured=="z" %}
                <img class="ribbon" src="/images/bronzeribbon_old.gif" />
            {% endif %}
            <p class="mininfo">
                <span class="title">
                    <a href="/fics/story/{{ story.StoryId }}/">{{ story.Title }}</a>
                </span>
                by
                <span class="author">
                    <a href="/user/{{ story.author.UserId }}/fics/">{{ story.author.DisplayName }}</a>
                </span>
                <span class="stars">{% autoescape false %}{{ story.stars }}{% endautoescape %}</span>
            </p>
            <p>
                <span class="rating">Rated: {{ story.rating }}</span>
                <span class="stars">{% autoescape false %}{{ story.stars }}{% endautoescape %}</span>
                {% if story.NumReviews > 0 %}<span class="reviews">[<a href="/fics/story/{{ story.StoryId }}/?reviews#reviews">Reviews: {{ story.NumReviews }}</a>]</span>{% endif %}
            </p>
        </div>
        <div class="storyblockinfo">
            <div class="summary{% if restrictSummaryHeight %} summary-box{% endif %}">
                <p class="metalabel">Summary:</p><p>{% autoescape false %}{{ story.Summary }}{% endautoescape %}</p>
            </div>
            <ul>
                <li>
                    <span class="metalabel">Tags:</span>
                    {% if story.tags|length > 0 %}
                        <ul class="taglist">
                            {% for tag in story.tags %}
                                <li>
                                    {# TODO: Link to search page #}
                                    <a href="/fics/search/?search={{ tag.Name }}"><span class="{{ tag.class }}">{{ tag.Name }}</span></a>
                                </li>
                            {% endfor %}
                        </ul>
                    {% else %}
                        None
                    {% endif %}
                </li>
                <li><span class="metalabel">Chapters:</span>{{ story.ChapterCount }}</li>
                <li>
                    <span class="metalabel">Completed:</span>{% if story.Completed %}Yes{% else %}No{% endif %}
                    <span class="metalabel">Word Count:</span>{{ story.WordCount }}
                    <span class="metalabel">Views:</span>{{ story.Views }}
                </li>
                <li>
                    <span class="metalabel">Published:</span>{{ story.DateCreated }}
                    {% if story.DateCreated != story.DateUpdated %}<span class="metalabel">Updated:</span>{{ story.DateUpdated }}{% endif %}
                </li>
            </ul>
        </div>
        <div class="storyblockfooter">
            {% if story.canEdit %}
                {# canFeature will always have canEdit #}
                [<a href="/fics/edit/{{ story.StoryId }}/">Edit</a>]
            {% endif %}
            {% if story.canDelete and story.ApprovalStatus=='A' %}
                [<a href="/fics/delete/{{ story.StoryId }}/">Delete</a>]
            {% elseif story.canUnDelete and story.ApprovalStatus=="D" %}
                [<a href="/fics/undelete/{{ story.StoryId }}/">Un-Delete</a>]
            {% endif %}
            {% if story.canFavorite %}
                <form id="favform" method="POST" accept-charset="UTF-8">
                    <input type="hidden" name="action" value="add-favorite" />
                    <input type="hidden" name="id" value="{{ story.StoryId }}" />
                </form>
                <a href="#" onclic="$('#favform')[0].submit();return false;">Add to Favorites</a>
            {% elseif story.canUnfavorite %}
                <form id="favform" method="POST" accept-charset="UTF-8">
                    <input type="hidden" name="action" value="remove-favorite" />
                    <input type="hidden" name="id" value="{{ story.StoryId }}" />
                </form>
                <a href="#" onclic="$('#favform')[0].submit();return false;">Remove from Favorites</a>
            {% endif %}
        </div>
    </div>
</div>
{% endblock %}
