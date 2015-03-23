{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
{% endblock %}

{% block content %}
    <div class="sidepanel">
        {% if post.ParentPostId != -1 %}
            <div class="parentbox">
                <p><strong>Parent Post</strong></p>
                <p><a href="/gallery/post/show/{{ post.ParentPostId }}/"># {{ post.ParentPostId }}</a></p>
            </div>
        {% endif %}
        {% if post.hasChildren %}
            <div class="parentbox">
                <p><strong>Child Posts</strong></p>
                <p><a href="/gallery/post/?search=parent%3A{{ post.PostId }}">here</a></p>
            </div>
        {% endif %}
        <h3>Search</h3>
        <div class="searchbox">
            <form action="/gallery/post/">
                <input name="search" type="textfield" />
            </form>
        </div>
        <hr />
        <h3>Tags:</h3>
        <div class="tagbox">
            <ul class="taglist">
                {% for category in post.tagCategories %}
                    <li class="tagcategory">
                        <strong>{{ category.name }}</strong>
                        <ul class="taglist">
                            {% for tag in category.tags %}
                                <li class="tag">
                                    <a href="/gallery/post/?search={{ tag.Name }}">{{ tag.displayName }}</a>
                                </li>
                            {% endfor %}
                        </ul>
                    </li>
                {% endfor %}
            </ul>
        </div>
        <h3>Statistics</h3>
        <div class="statbox">
            <ul class="statlist">
                <li>ID: {{ post.PostId }}</li>
                {% if post.Source != "" %}<li>Source: <a href="{{ post.Source }}">{{ post.Source }}</a></li>{% endif %}
                <li>Posted: {% autoescape false %}{{ post.postedHtml }}{% endautoescape %}</li>
                <li>Rating: {% autoescape false %}{{ post.ratingHtml }}{% endautoescape %}</li>
                <li>Score: {{ post.Score }}</li>
                {% if post.FileSize != "" %}<li>Size: {{ post.FileSize }}</li>{% endif %}
                <li>Views: 0</li>
            </ul>
        </div>
    </div>
    <div class="mainpanel">
        <p>
            <img class="previewImg" src="{{ previewUrl }}" />
        </p>
        <p>
            <a href="/gallery/post/edit/{{ post.PostId }}/">Edit</a> | <a href="{{ downloadUrl }}">Download</a>
        </p>
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
