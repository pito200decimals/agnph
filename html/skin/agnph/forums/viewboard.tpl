{% extends 'base.tpl' %}

{% block content %}
{#
    If main lobby page, will contain a variable called "home" = array of lobbies. Each lobby has a field called "childBoards", which is another array of lobbies.
    If sublobby board, will contain a variable called "board". This will contain a field called "threads" which is an array of posts (threads) for the given page.
#}
    {% if home %}
        {# Display groups of boards #}
        {% autoescape false %}{{ crumbs }}{% endautoescape %}
        {% for lobby in home %}
            <div style="margin: 10px;">
                <h3><a name="b{{ lobby.LobbyId }}">{{ lobby.Name }}</a></h3>
                {% for board in lobby.childBoards %}
                    <p>
                    <a href="/forums/board/{{ board.LobbyId }}/" name="b{{ board.LobbyId }}">{{ board.Name }}</a><br />
                    {{ board.Description }}
                    </p>
                {% endfor %}
            </div>
        {% endfor %}
        {% autoescape false %}{{ crumbs }}{% endautoescape %}
    {% elseif board %}
        {# Display threads in a board #}
        {% autoescape false %}{{ crumbs }}{% endautoescape %}
        <div style="margin: 10px;">
            <h3>{{ board.Name }}</h3>
            <a href="/forums/create/{{ board.LobbyId }}/">Create New Thread</a>
            {% if page_iterator %}
                {% autoescape false %}
                    <div style="margin:15px;">
                        Pages: {{ page_iterator }}
                    </div>
                {% endautoescape %}
            {% else %}
                <br />
            {% endif %}
            {% if board.threads|length > 0 %}
                {% for thread in board.threads %}
                    <p>
                    {% if thread.Sticky %}
                        [STICKY]
                    {% endif %}
                    <a href="/forums/thread/{{ thread.PostId }}/">{{ thread.Title }}</a><br />
                    Started by {{ thread.creator.DisplayName }}<br />
                    <small>Created {{ thread.PostDate }}</small></p>
                {% endfor %}
            {% else %}
                No threads to display.
            {% endif %}
            {% if page_iterator %}
                {% autoescape false %}
                    <div style="margin:15px;">
                        Pages: {{ page_iterator }}
                    </div>
                {% endautoescape %}
            {% else %}
                <br />
            {% endif %}
            <a href="/forums/create/{{ lobby.LobbyId }}/">Create New Thread</a>
        </div>
        {% autoescape false %}{{ crumbs }}{% endautoescape %}
    {% elseif content %}
        {{ content }}
    {% else %}
        No content here.
    {% endif %}
{% endblock %}
