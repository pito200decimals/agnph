{% extends 'forums/view_board_base.tpl' %}

{% macro boardgroup(boardgroup, linkName) %}
    <table class="board-group">
        <thead>
            <tr>
                <td colspan="4">
                    <span class="board-group-title">
                        {% if linkName %}
                            <a href="/forums/board/{{ boardgroup.Name|lower|url_encode }}/">{{ boardgroup.Name }}</a>
                        {% else %}
                            {{ boardgroup.Name }}
                        {% endif %}
                    </span>
                </td>
            </tr>
        </thead>
        <tbody>
            {% for board in boardgroup.childBoards %}
                <tr class="board-row">
                    <td class="status">
                        {% if board.unread %}
                            <img src="/images/unread-board.png" title="Unread posts" />
                        {% else %}
                            <img src="/images/read-board.png" title="All posts read" />
                        {% endif %}
                    </td>
                    <td class="board-desc">
                        <span><a href="/forums/board/{{ board.Name|lower|url_encode }}/">{{ board.Name }}</a></span>
                        <div>
                            {{ board.Description }}
                        </div>
                    </td>
                    <td class="board-stats">
                        <ul>
                            <li>{{ board.NumPosts }} posts</li>
                            <li>{{ board.NumThreads }} threads</li>
                        </ul>
                    </td>
                    <td class="lastpost">
                        {% if board.lastPost %}
                            <ul>
                                <li>Last Post by <a href="/user/{{ board.lastPost.user.UserId }}/">{{ board.lastPost.user.DisplayName }}</a></li>
                                <li>in <a href="{{ board.lastPost.url }}">{{ board.lastPost.Title }}</a></li>
                                <li>on {{ board.lastPost.date }}</li>
                            </ul>
                        {% endif %}
                    </td>
                </tr>
            {% endfor %}
        </tbody>
    </table>
{% endmacro %}

{% use 'forums/view_board_threadlist.tpl' %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
    {% endif %}
{% endblock %}

{% block content %}
    {{ block('banner') }}
    {% if board.BoardId == -1 %}
        {% for board in board.childBoards %}
            {{ _self.boardgroup(board, true) }}
        {% endfor %}
    {% else %}
        {{ _self.boardgroup(board, false) }}
    {% endif %}
    {# TODO: Determine if these should be visible when there are no threads #}
    {% if board.BoardId != -1 %}
        {{ block('threadList') }}
    {% endif %}
    {{ block('actionbar') }}
    <hr />
    {{ block('help_block') }}
{% endblock %}