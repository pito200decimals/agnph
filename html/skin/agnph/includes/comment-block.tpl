// Comment object consists of:
// - user
// - date
// - title (optional)
// - text

{% block comment %}
    <li class="comment">
        <img class="comment-avatarimg" src="{{ comment.user.avatarURL }}" />
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
