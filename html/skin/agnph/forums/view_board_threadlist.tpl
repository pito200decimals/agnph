{% block sortArrow %}
    {% if orderParam == "desc" %}
        ▼
    {% else %}
        ▲
    {% endif %}
{% endblock %}

{% block threadList %}
    <div class="board-actionbar">
        <a href="/forums/compose/?action=create&id={{ board.BoardId }}">Create Thread (TODO)</a>
    </div>
    {% if threads|length > 0 %}
        <table class="list-table">
            <thead>
                <tr>
                    <td></td>
                    <td><a href="{{ titleSortUrl }}">Title</a>{% if sortParam == "title" %}{{ block('sortArrow') }}{% endif %}</td>
                    <td><a href="{{ repliesSortUrl }}">Replies</a>{% if sortParam == "replies" %}{{ block('sortArrow') }}{% endif %} / <a href="{{ viewsSortUrl }}">Views</a>{% if sortParam == "views" %}{{ block('sortArrow') }}{% endif %}</td>
                    <td><a href="{{ lastpostSortUrl }}">Last Post</a>{% if sortParam == "lastpost" %}{{ block('sortArrow') }}{% endif %}</td>
                </tr>
            </thead>
            <tbody>
                {% for thread in threads %}
                    <tr>
                        <td class="status">
                            {% if thread.Sticky %}[STICKY]{% endif %}
                            {% if thread.Locked %}[LOCKED]{% endif %}
                            {% if thread.unread %}
                                <img src="/images/unread-board.png" />
                            {% else %}
                                <img src="/images/read-board.png" />
                            {% endif %}
                        </td>
                        <td>
                            <a href="/forums/thread/{{ thread.PostId }}/">{{ thread.Title }}</a><br />
                            Started by <a href="/user/{{ thread.user.UserId }}/">{{ thread.user.DisplayName }}</a>
                        </td>
                        <td>
                            {{ thread.Replies }} replies<br />
                            {{ thread.Views }} views
                        </td>
                        <td class="lastpost">
                            {% if thread.lastPost %}
                                <ul>
                                    <li>{{ thread.lastPost.date }}</li>
                                    <li>by <a href="/user/{{ thread.lastPost.user.UserId }}/">{{ thread.lastPost.user.DisplayName }}</a></li>
                                </ul>
                            {% endif %}
                        </td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>
        {#
        <ul class="list">
            {% for thread in threads %}
                <li>{{ thread.Title }}</li>
            {% endfor %}
        </ul>#}
        <div class="iterator">
            {% autoescape false %}{{ iterator }}{% endautoescape %}
        </div>
    {% else %}
        No threads found
    {% endif %}
{% endblock %}
