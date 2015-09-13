{% extends 'forums/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ asset('/forums/style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/forums/board-style.css') }}" />
{% endblock %}

{% block actionbar %}
    {% if user %}
        <ul class="forums-actionbar">
            {% if board.childBoards|length > 0 %}
                {# Allow mass-marking-as-read when not a leaf board #}
                {% if board.BoardId == -1 %}
                    <li><a href="/forums/mark-all-read/">Mark all as read</a></li>
                {% else %}
                    <li><a href="/forums/mark-all-read/?board={{ board.BoardId }}">Mark all as Read</a></li>
                {% endif %}
                {% if canLockBoard %}
                    <br />
                {% endif %}
            {% endif %}
            {% if canLockBoard %}
                {% if board.Locked %}
                    <form id="unlock-board-form" method="POST" accept-encoding="UTF-8" hidden>
                        <input type="hidden" name="action" value="unlock" />
                    </form>
                    <li><a href="/forums/unlock-board/" onclick="document.getElementById('unlock-board-form').submit();return false;">Unlock Board</a></li>
                {% else %}
                    <form id="lock-board-form" method="POST" accept-encoding="UTF-8" hidden>
                        <input type="hidden" name="action" value="lock" />
                    </form>
                    <li><a href="/forums/lock-board/" onclick="document.getElementById('lock-board-form').submit();return false;">Lock Board</a></li>
                {% endif %}
                {% if board.PrivateBoard %}
                    <form id="public-board-form" method="POST" accept-encoding="UTF-8" hidden>
                        <input type="hidden" name="action" value="mark-public" />
                    </form>
                    <li><a href="/forums/public-board/" onclick="document.getElementById('public-board-form').submit();return false;">Mark Public</a></li>
                {% else %}
                    <form id="private-board-form" method="POST" accept-encoding="UTF-8" hidden>
                        <input type="hidden" name="action" value="mark-private" />
                    </form>
                    <li><a href="/forums/private-board/" onclick="document.getElementById('private-board-form').submit();return false;">Mark Private</a></li>
                {% endif %}
            {% endif %}
        </ul>
        <div class="Clear">&nbsp;</div>
    {% endif %}
{% endblock %}

{% block content %}
{% endblock %}
