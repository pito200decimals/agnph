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
                            <img src="/images/unread-board.png" />
                        {% else %}
                            <img src="/images/read-board.png" />
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
                            <li>Posts: {{ board.NumPosts }}</li>
                            <li>Threads: {{ board.NumThreads }}</li>
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

{% block content %}
    {% if board.BoardId == -1 %}
        {% for board in board.childBoards %}
            {{ _self.boardgroup(board, true) }}
        {% endfor %}
    {% else %}
        {{ _self.boardgroup(board, false) }}
    {% endif %}
    {% if threads|length > 0 %}
        <hr />
        {{ block('threadList') }}
    {% endif %}
    <p>
    TODO: Help block
    </p>
{% endblock %}