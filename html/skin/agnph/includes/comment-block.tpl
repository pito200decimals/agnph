// Comment object consists of:
// - user
// - date
// - title (optional)
// - text

{% block comment %}
    <li class="comment">
        {% if comment.anchor %}<a name="{{ comment.anchor }}" />{% endif %}
        <img class="comment-avatarimg" src="{{ comment.user.avatarURL }}" />
        <div class="commentheader">
            {% for action in comment.actions|reverse %}
                <form {% if action.url %}action="{{ action.url }}" {% endif %}class="edit-comment-form" method="{% if action.method %}{{ action.method }}{% else %}POST{% endif %}" accept-charset="UTF-8">
                    <input type="hidden" name="action" value="{{ action.action }}" />
                    <input type="hidden" name="id" value="{{ comment.id }}" />
                    <input type="submit" value="{{ action.label }}" {% if action.confirmMsg %}onclick="return confirm('{{ action.confirmMsg }}');" {% endif %}/>
                </form>
            {% endfor %}
            <a href="/user/{{ comment.user.UserId }}/">{{ comment.user.DisplayName }}</a><br />
            <span>Date: {{ comment.date }}</span>{% if comment.editDate %}<span>(Edited: {{ comment.editDate }})</span>{% endif %}{% if comment.title %}&nbsp;<span>Title: {{ comment.title }}</span>{% endif %}
        </div>
        <div class="commenttext">
            {% autoescape false %}
                {{ comment.text }}
            {% endautoescape %}
        </div>
    </li>
{% endblock %}
