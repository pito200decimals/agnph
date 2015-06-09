{% block comment %}
    <li class="comment">
        {% if comment.commenter.Avatar|length > 0 %}
            {# avatar image #}
            <img class="avatarimg" src="{{ comment.commenter.Avatar }}" />
        {% else %}
            {# default avatar image #}
            <img class="avatarimg" src="http://i.imgur.com/CKd8AGC.png" />
        {% endif %}
        <p class="commentheader">
            <a href="/user/{{ comment.commenter.UserId }}/">{{ comment.commenter.DisplayName }}</a><br />
            <span>Date: {{ comment.date }}</span>
        </p>
        <div class="commenttext">
            {% autoescape false %}
                {% if comment.ReviewText %}
                    {{ comment.ReviewText }}
                {% elseif comment.CommentText %}
                    {{ comment.CommentText }}
                {% endif %}
            {% endautoescape %}
        </div>
    </li>
{% endblock %}
