{# Knows about variable named 'post' corresponding mostly to the database row for that post. #}
<div class="postblock">
    <a name="p{{ post.PostId }}" />
    <div class="postuserbox">
        <div class="postuser">
            <p>
                <a href="/user/{{ post.poster.UserId }}/"><strong>{{ post.poster.DisplayName }}</strong></a>
            </p>
            <p>
                {# Admin titles & badges go here #}
            </p>
            <p>
                <a href="/user/{{ post.poster.UserId }}/">
                    {% if post.poster.Avatar|length > 0 %}
                        {# avatar image #}
                        <img class="avatarimg" src="{{ post.poster.Avatar }}" />
                    {% else %}
                        {# default avatar image #}
                        <img class="avatarimg" src="http://i.imgur.com/CKd8AGC.png" />
                    {% endif %}
                </a>
            </p>
            <p>
                {{ post.poster.Title }}
            </p>
            <p>
                {# User statistics like post count go here #}
            </p>
        </div>
    </div>
    <div class="postcontentbox">
        <div class="postheader">
            <div class="postactions">
                {% if post.modifyLink %}<a href="/forums/edit/{{ post.PostId }}/">Modify</a>{% endif %}
                {% if post.deleteLink %}<a href="#" onclick="return deletePost({{ post.PostId }})">Delete</a>{% endif %}
            </div>
            <em>{{ post.Title }}</em><br />
            {% if post.PostDate %}
                <small>(Posted {{ post.PostDate }}
                        {% if post.EditDate %}, Edited {{ post.EditDate }}{% endif %}
                    )</small>
            {% endif %}
        </div>
        <div class="postmessage">
            {% autoescape false %}
            {{ post.Content }}
            {% endautoescape %}
        </div>
        <hr />
        {% if post.PostIP %}
            [ADMIN ONLY] Posted from: {{ post.PostIP }}
            <hr />
        {% endif %}
        <div class="postsig">
            {{ post.poster.Signature }}
        </div>
    </div>
</div>
