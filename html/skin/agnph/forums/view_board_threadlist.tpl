{% block sortArrow %}
    {% if orderParam == "desc" %}
        ▼
    {% else %}
        ▲
    {% endif %}
{% endblock %}

{% block threadList %}
    {% if user %}
        <ul class="forums-actionbar">
            <li><a href="/forums/mark-all-read/?board={{ board.BoardId }}">Mark posts as Read</a></li>
            {% if canCreateThread %}
                <li><a href="/forums/compose/?action=create&id={{ board.BoardId }}">Create Thread</a></li>
            {% endif %}
        </ul>
    {% endif %}
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
                {% if threads|length > 0 %}
                    {% for thread in threads %}
                        <tr>
                            <td class="status">
                                {% if thread.unread %}
                                    <img src="/images/unread-board.png" title="Unread posts" />
                                {% else %}
                                    <img src="/images/read-board.png" title="All posts read" />
                                {% endif %}
                            </td>
                            <td>
                                {% if thread.Sticky %}<img class="icon" src="/images/sticky.gif" />{% endif %}
                                {% if thread.Locked %}<img class="icon" src="/images/locked.png" />{% endif %}
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
                {% else %}
                    <tr>
                        <td class="status"></td>
                        <td></td>
                        <td>No threads found</td>
                        <td class="lastpost"></td>
                    </tr>
                {% endif %}
            </tbody>
        </table>
        <div class="iterator">
            {% autoescape false %}{{ iterator }}{% endautoescape %}
        </div>
{% endblock %}
