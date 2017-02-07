{% block storyblock %}
    {% import 'fics/stars.tpl' as stars %}
    <div class="storyblock">
        <div class="storyblockcontainer">
            <div class="storyblockheader">
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
                <div class="mininfo">
                    <span class="title">
                        <a href="/fics/story/{{ story.StoryId }}/">{{ story.Title }}</a>
                    </span>
                    by
                    <ul class="author-list">
                        <li class="author">
                            <a href="/user/{{ story.author.UserId }}/fics/">{{ story.author.DisplayName }}</a>
                        </li>
                        {% for author in story.coauthors %}
                            <li class="coauthor">
                                <a href="/user/{{ author.UserId }}/fics/">{{ author.DisplayName }}</a>
                            </li>
                        {% endfor %}
                    </ul>
                    <span class="stars">
                        {{ stars.stars(story) }}
                    </span>
                </div>
                <div>
                    <span class="rating">Rated: {{ story.rating }}</span>
                    <span class="stars">
                        {{ stars.stars(story) }}
                    </span>
                    {% if not story.shortDesc %}
                    {% if story.NumReviews > 0 %}<span class="reviews">[<a href="/fics/story/{{ story.StoryId }}/?reviews#reviews">Reviews:&nbsp;{{ story.NumReviews }}</a>]</span>{% endif %}
                    {% endif %}
                </div>
            </div>
            <div class="storyblockinfo">
                <div class="summary{% if restrictSummaryHeight %} summary-box{% endif %}">
                    <p class="metalabel">Summary:</p><p class="metavalue">{% autoescape false %}{{ story.Summary }}{% endautoescape %}</p>
                </div>
                <ul class="meta-list">
                    <li>
                        <span class="metalabel">Tags:</span>
                        <span class="metavalue">
                            {% if story.tags|length > 0 %}
                                <ul class="taglist">
                                    {% for tag in story.tags %}
                                        <li class="tag">
                                            <a href="/fics/browse/?search=tag%3A{{ tag.quotedName|url_encode }}" class="{{ tag.Type|lower }}typetag">{{ tag.displayName }}</a>
                                        </li>
                                    {% endfor %}
                                </ul>
                            {% else %}
                                <span class="none-tag">None</span>
                            {% endif %}
                        </span>
                    </li>
                    <li><span class="metalabel">Chapters:</span> <span class="metavalue">{{ story.ChapterCount }}</span></li>
                    {% if not story.shortDesc %}
                    <li>
                        <span class="metalabel">Completed:</span> <span class="metavalue">{% if story.Completed %}Yes{% else %}No{% endif %}</span>
                        <span class="metalabel">Word Count:</span> <span class="metavalue">{{ story.WordCount }}</span>
                        <span class="metalabel">Views:</span> <span class="metavalue">{{ story.Views }}</span>
                    </li>
                    <li>
                        <span class="metalabel">Published:</span> <span class="metavalue">{{ story.DateCreated }}</span>
                        {% if story.DateCreated != story.DateUpdated %}<span class="metalabel">Updated:</span> <span class="metavalue">{{ story.DateUpdated }}</span>{% endif %}
                    </li>
                    {% endif %}
                </ul>
            </div>
            {% if not story.shortDesc %}
                {% if story.canEdit or (story.canDelete and story.ApprovalStatus=='A') or (story.canUnDelete and story.ApprovalStatus=='D') or story.canFavorite or story.canUnfavorite %}
                    <div class="storyblockfooter">
                        {% if story.canEdit %}
                            {# canFeature will always have canEdit #}
                            [<a href="/fics/edit/{{ story.StoryId }}/">Edit</a>]
                        {% endif %}
                        {% if story.canDelete and story.ApprovalStatus=='A' %}
                            [<a href="/fics/delete/{{ story.StoryId }}/">Delete</a>]
                        {% elseif story.canUnDelete and story.ApprovalStatus=='D' %}
                            [<a href="/fics/undelete/{{ story.StoryId }}/">Un-Delete</a>]
                        {% endif %}
                        {% if story.canFavorite %}
                            <form id="favform-{{ story.StoryId }}" action="/fics/favorite/" method="POST" accept-charset="UTF-8">
                                <input type="hidden" name="action" value="add-favorite" />
                                <input type="hidden" name="id" value="{{ story.StoryId }}" />
                            </form>
                            [<a href="#" onclick="$('#favform-{{ story.StoryId }}')[0].submit();return false;">Add to Favorites</a>]
                        {% elseif story.canUnfavorite %}
                            <form id="favform-{{ story.StoryId }}" action="/fics/favorite/" method="POST" accept-charset="UTF-8">
                                <input type="hidden" name="action" value="remove-favorite" />
                                <input type="hidden" name="id" value="{{ story.StoryId }}" />
                            </form>
                            [<a href="#" onclick="$('#favform-{{ story.StoryId }}')[0].submit();return false;">Remove from Favorites</a>]
                        {% endif %}
                    </div>
                {% endif %}
            {% endif %}
        </div>
    </div>
{% endblock %}
