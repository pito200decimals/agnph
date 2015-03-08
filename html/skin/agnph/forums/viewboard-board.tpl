{# Display threads in a board #}

{# Breadcrumbs #}
<span class="crumbs">{% autoescape false %}{{ crumbs }}{% endautoescape %}</span>

<div style="margin: 10px;">
    <h3>{{ board.Name }}</h3>
    <div class="threadactions">
        <ul>
            <li><a href="/forums/create/{{ board.LobbyId }}/">Create New Thread</a></li>
        </ul>
    </div>
    {% if page_iterator %}{% autoescape false %}<span class="iterator">Pages: {{ page_iterator }}</span>{% endautoescape %}{% endif %}
    <div class="threadlist">
        {% if board.threads|length > 0 %}
                {% for thread in board.threads %}
                    <div class="thread">
                        <small class="createdlabel">Created {{ thread.PostDate }}</small>
                        <h4>
                            {% if thread.Sticky %}
                                [STICKY]
                            {% endif %}
                            {% if thread.unread %}
                                [<a href="{{ thread.unread_link }}">NEW</a>]
                            {% endif %}
                            <a href="/forums/thread/{{ thread.PostId }}/">{{ thread.Title }}</a>
                        </h4>
                        <small class="startedlabel">Started by {{ thread.creator.DisplayName }}</small>
                    </div>
                {% endfor %}
        {% else %}
            No threads to display.
        {% endif %}
    </div>
    {% if page_iterator %}{% autoescape false %}<span class="iterator">Pages: {{ page_iterator }}</span>{% endautoescape %}{% endif %}
    <div class="threadactions">
        <ul>
            <li><a href="/forums/create/{{ board.LobbyId }}/">Create New Thread</a></li>
        </ul>
    </div>
</div>

{# Breadcrumbs #}
<span class="crumbs">{% autoescape false %}{{ crumbs }}{% endautoescape %}</span>
