{% extends 'base.tpl' %}

{% block content %}
    {% if rootLobbies %}
        {# Display groups of boards #}
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
        {# Display threads in a board #}
        <div style="margin: 10px;">
            <h3>{{ lobby.Name }}</h3>
            <a href="/forums/create/{{ lobby.LobbyId }}/">Create New Thread</a>
            {% if page_iterator %}
                {% autoescape false %}
                    <div style="margin:15px;">
                        Pages: {{ page_iterator }}
                    </div>
                {% endautoescape %}
            {% else %}
                <br />
            {% endif %}
            {% if lobby.threads|length > 0 %}
                {% for thread in lobby.threads %}
                    <p>
                    {% if thread.Sticky %}
                        [STICKY]
                    {% endif %}
                    <a href="/forums/thread/{{ thread.ThreadId }}/">{{ thread.Title }}</a><br />
                    Started by {{ thread.creator.DisplayName }}<br />
                    <small>Created {{ thread.CreateDate }}</small></p>
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
    {% elseif content %}
        {{ content }}
    {% else %}
        No content here.
    {% endif %}
{% endblock %}
