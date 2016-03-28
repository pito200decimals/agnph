// Comment object consists of:
// - user
// - date
// - title (optional)
// - text

{% block comment %}
    <li class="comment">
        {% if comment.anchor %}<a name="{{ comment.anchor }}"></a>{% endif %}
        <div class="comment-side-panel">
            <div>
                <a href="/user/{{ comment.user.UserId }}/">
                    <img class="comment-avatarimg" src="{{ comment.user.avatarURL }}" />
                </a>
                <div class="Clear">&nbsp;</div>
            </div>
            {% if comment.user.Title|length > 0 %}
                <span class="comment-side-panel-label">{{ comment.user.Title }}</span>
            {% endif %}
        </div>
        <div class="comment-content">
            <div class="commentheader">
                {% for action in comment.actions|reverse %}
                    <form {% if action.url %}action="{{ action.url }}" {% endif %}class="edit-comment-form" method="{% if action.method %}{{ action.method }}{% else %}POST{% endif %}" accept-charset="UTF-8">
                        <input type="hidden" name="action" value="{{ action.action }}" />
                        {% if action.id %}<input type="hidden" name="id" value="{{ action.id }}" />{% else %}<input type="hidden" name="id" value="{{ comment.id }}" />{% endif %}
                        {% for kv in action.kv %}
                            <input type="hidden" name="{{ kv.key }}" value="{{ kv.value }}" />
                        {% endfor %}
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
        </div>
        <div class="Clear">&nbsp;</div>
        {% if comment.anchor %}
            <div><small class="direct-link"><a href="#{{ comment.anchor }}">Link to post</a></small></div>
            <div class="Clear">&nbsp;</div>
        {% endif %}
    </li>
{% endblock %}
