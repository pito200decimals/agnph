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
                    <td class="desktop-only"><a href="{{ repliesSortUrl }}">Replies</a>{% if sortParam == "replies" %}{{ block('sortArrow') }}{% endif %} / <a href="{{ viewsSortUrl }}">Views</a>{% if sortParam == "views" %}{{ block('sortArrow') }}{% endif %}</td>
                    <td><a href="{{ lastpostSortUrl }}">Last Post</a>{% if sortParam == "lastpost" %}{{ block('sortArrow') }}{% endif %}</td>
                </tr>
            </thead>
            <tbody>
                {% if threads|length > 0 %}
                    {% for thread in threads %}
                        <tr>
                            <td class="status">
                                {% if thread.unread %}
                                    {% if thread.first_unread_url %}
                                        <a href="{{ thread.first_unread_url }}"><img src="/images/unread-board.png" title="Unread posts" /></a>
                                    {% else %}
                                        {# Link is missing for some reason #}
                                        <img src="/images/unread-board.png" title="Unread posts" />
                                    {% endif %}
                                {% else %}
                                    <img src="/images/read-board.png" title="All posts read" />
                                {% endif %}
                            </td>
                            <td>
                                {% if thread.Sticky %}<img class="icon" src="/images/sticky.gif" />{% endif %}
                                {% if thread.Locked %}<img class="icon" src="/images/locked.png" />{% endif %}
                                <span class="thread-title"><a href="/forums/thread/{{ thread.PostId }}/">{{ thread.Title }}</a></span>
                                {% if thread.first_unread_url %}<span class="new-post-label"><a href="{{ thread.first_unread_url }}">New</a></span>{% endif %}
                                <br />
                                <span class="thread-subline">Started by <a href="/user/{{ thread.user.UserId }}/">{{ thread.user.DisplayName }}</a></span>
                            </td>
                            <td class="desktop-only">
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
                        <td class="desktop-only"></td>
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
