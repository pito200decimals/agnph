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
    </div>
    <div class="mainpanel">
        {% if posts|length > 0 %}
            {# Display searc index. #}
            {% for post in posts %}
                <a href="/gallery/post/show/{{ post.PostId }}/">
                    <div class="postsquare">
                        <div class="postsquarepreview">
                            <img class="postsquarepreview" src="{{ post.thumbnail }}" />
                        </div>
                        <div class="postlabel">
                            &nbsp;
                        </div>
                    </div>
                </a>
            {% endfor %}
        {% else %}
            {# No posts here. #}
            No posts matched your search.
        {% endif %}
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
