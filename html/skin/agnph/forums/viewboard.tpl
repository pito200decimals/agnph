{% extends 'base.tpl' %}

{% block content %}
    {% if rootLobbies %}
        {% for root in rootLobbies %}
            <div style="margin: 10px;">
                <h3>{{ root.Name }}</h3>
                {% for lobby in root.lobbies %}
                    <p>
                    <a href="/forums/board/{{ lobby.LobbyId }}/">{{ lobby.Name }}</a><br />
                    {{ lobby.Description }}
                    </p>
                {% endfor %}
            </div>
        {% endfor %}
    {% elseif lobby %}
        <div style="margin: 10px;">
            <h3>{{ lobby.Name }}</h3>
            <ul>
            {% for thread in lobby.threads %}
                <li><a href="/forums/thread/{{ thread.ThreadId }}/">{{ thread.Title }}</a></li>
            {% endfor %}
            </ul>
        </div>
    {% elseif content %}
        {{ content }}
    {% else %}
        No content here.
    {% endif %}
{% endblock %}
