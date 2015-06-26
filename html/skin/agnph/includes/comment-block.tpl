// Comment object consists of:
// - user
// - date
// - title (optional)
// - text

{% block comment %}
    <li class="comment">
        {% if comment.user.Avatar|length > 0 %}
            {# avatar image #}
            <img class="comment-avatarimg" src="{{ comment.user.Avatar }}" />
        {% else %}
            {# default avatar image #}
            <img class="comment-avatarimg" src="http://i.imgur.com/CKd8AGC.png" />
        {% endif %}
        <p class="commentheader">
            <a href="/user/{{ comment.user.UserId }}/">{{ comment.user.DisplayName }}</a><br />
            <span>Date: {{ comment.date }}</span>{% if comment.title %}&nbsp;<span>Title: {{ comment.title }}</span>{% endif %}
        </p>
        <div class="commenttext">
            {% autoescape false %}
                {{ comment.text }}
            {% endautoescape %}
        </div>
    </li>
{% endblock %}
