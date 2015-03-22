{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
{% endblock %}

{% block content %}
    <div class="sidepanel">
        <h3>Search</h3>
        <div class="searchbox">
            <form>
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
