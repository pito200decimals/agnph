{% extends 'forums/viewboard.tpl' %} {# Include style info #}
{# Display groups of boards #}

{% block content %}
    {# Breadcrumbs #}
    <span class="crumbs">{% autoescape false %}{{ crumbs }}{% endautoescape %}</span>

    <div class="allgroups">
        {% for lobby in home %}
            <div class="boardgroup">
                <h3 class="boardgrouptitle"><a name="b{{ lobby.LobbyId }}">{{ lobby.Name }}</a></h3>
                {% for board in lobby.childBoards %}
                    <div class="board">
                    <h4>{% if board.unread %}[NEW]{% endif %}<a href="/forums/board/{{ board.LobbyId }}/" name="b{{ board.LobbyId }}">{{ board.Name }}</a></h4>
                    <span class="boarddesc">{{ board.Description }}</span>
                    </div>
                {% endfor %}
            </div>
        {% endfor %}
    </div>

    {# Breadcrumbs #}
    <span class="crumbs">{% autoescape false %}{{ crumbs }}{% endautoescape %}</span>
{% endblock %}