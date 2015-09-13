{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ asset('/forums/style.css') }}" />
{% endblock %}

{% block section_navigation %}
    <ul class="section-nav">
        {{ block('breadcrumb_block_recursive') }}
    </ul>
{% endblock %}

{% block breadcrumb_block %}
    <ul class="breadcrumb">{{ block('breadcrumb_block_recursive') }}</ul>
{% endblock %}

{% block breadcrumb_block_recursive %}
    {% if board and board.BoardId != -1 %}
        {% set oldBoard = board %}
        {% set board = board.parentBoard %}
        {{ block('breadcrumb_block_recursive') }}<li> » <a href="{% if oldBoard.linkUrl %}{{ oldBoard.linkUrl }}{% else %}/forums/board/{{ oldBoard.Name|lower|url_encode }}/{% endif %}">{{ oldBoard.Name }}</a></li>
    {% else %}
        <li><a href="/forums/board/">Index</a></li>
    {% endif %}
{% endblock %}

{% block help_block %}
    <div class="help-block">
        <ul>
            <li><img src="/images/read-board.png" /> = Read</li>
            <li><img src="/images/unread-board.png" /> = Unread</li>
        </ul>
    </div>
{% endblock %}

{% block content %}
{% endblock %}
