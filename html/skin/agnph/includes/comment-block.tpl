// Comment object consists of:
// - user
// - date
// - title (optional)
// - text

{% block comment %}
    <li class="comment">
        <img class="comment-avatarimg" src="{{ comment.user.avatarURL }}" />
        {% if comment.canDelete %}<form method="POST" accept-charset="UTF-8">{% endif %}
            <p class="commentheader">
                {% if comment.canDelete %}
                    <span style="float: right; display: block;">
                        <input type="hidden" name="action" value="delete-comment" />
                        <input type="hidden" name="id" value="{{ comment.id }}" />
                        <input type="submit" value="Delete" />
                    </span>
                {% endif %}
                <a href="/user/{{ comment.user.UserId }}/">{{ comment.user.DisplayName }}</a><br />
                <span>Date: {{ comment.date }}</span>{% if comment.title %}&nbsp;<span>Title: {{ comment.title }}</span>{% endif %}
            </p>
        {% if comment.canDelete %}</form>{% endif %}
        <div class="commenttext">
            {% autoescape false %}
                {{ comment.text }}
            {% endautoescape %}
        </div>
    </li>
{% endblock %}
