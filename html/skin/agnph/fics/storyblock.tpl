{% block storyblock %}
<div class="storyblock">
    <div class="storyblockcontainer">
        <div class="storyblockheader">
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
                <span class="reviews">[Reviews: {{ story.NumReviews }}]</span>
            </p>
        </div>
        <div class="storyblockinfo">
            <div class="summary{% if restrictSummaryHeight %} summary-box{% endif %}">
                <p class="metalabel">Summary:</p><p>{% autoescape false %}{{ story.Summary }}{% endautoescape %}</p>
            </div>
            <ul>
                <li><span class="metalabel">Tags:</span>N/A</li>
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
            {% if canEdit or user.UserId == story.AuthorUserId %}
                [<a href="/fics/edit/{{ story.StoryId }}/">Edit</a>]
            {% endif %}
        </div>
    </div>
</div>
{% endblock %}
