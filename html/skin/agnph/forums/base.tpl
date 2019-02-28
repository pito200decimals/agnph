{% extends 'base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/forums/style.css')|raw }}
    {{ inline_css_asset('/comments-style.css')|raw }}
{% endblock %}

{# In base template, use section navigation for breadcrumbs #}
{% block section_navigation %}
    <ul class="section-nav font-scalable">
        {{ block('breadcrumb_block_recursive') }}
    </ul>
{% endblock %}

{# Separate bar on top of all content, if a skin wants to display crumbs differently. #}
{% block breadcrumb_bar %}
{% endblock %}

{% block breadcrumb_block_recursive %}
    {% if board and board.BoardId != -1 %}
        {% set oldBoard = board %}
        {% set board = board.parentBoard %}
        {{ block('breadcrumb_block_recursive') }}
        <li>»</li>
        <li>
            <a href="{% if oldBoard.linkUrl %}{{ oldBoard.linkUrl }}{% else %}/forums/board/{{ oldBoard.Name|lower|url_encode }}/{% endif %}">
                {{ oldBoard.Name }}
            </a>
        </li>
    {% else %}
        <li><a href="/forums/board/">Index</a></li>
    {% endif %}
{% endblock %}

{% block help_block %}
    {#
    <div class="forums-help-block">
        <ul>
            <li><img src="/images/read-board.png" /> = Read</li>
            <li><img src="/images/unread-board.png" /> = Unread</li>
        </ul>
    </div>
    #}
{% endblock %}

{% block content %}
{% endblock %}
