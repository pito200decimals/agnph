{% extends 'forums/view_board_base.tpl' %}

{% block top_level_board_block %}
        <ul class="board-group-list">
            {% for board in board.childBoards %}
                <li>
                    <h3 class="board-title"><a href="/forums/board/{{ board.Name|lower|url_encode }}/">{{ board.Name }}</a></h3>
                    {% if board.childBoards|length > 0 %}
                        {{ block('board_group_block') }}
                    {% else %}
                        <span>{{ board.Description }}</span>
                    {% endif %}
                </li>
            {% endfor %}
        </ul>
{% endblock %}

{% block board_group_block %}
    <ul class="board-group">
        {% for board in board.childBoards %}
            <li>
                <h4 class="board-title"><a href="/forums/board/{{ board.Name|lower|url_encode }}/">{{ board.Name }}</a></h4>
                <span>{{ board.Description }}</span>
            </li>
        {% endfor %}
    </ul>
{% endblock %}

{% block content %}
    {% if board.BoardId != -1 %}
        <h3>{{ board.Name }}</h3>
    {% endif %}
    {{ block('top_level_board_block') }}
{% endblock %}
