{% extends 'forums/view_board_base.tpl' %}

{% use 'forums/view_board_threadlist.tpl' %}

{% block content %}
    {{ block('breadcrumb_bar') }}
    {% if board.BoardId != -1 %}
        <h3>{{ board.Name }}</h3>
    {% endif %}
    {{ block('banner') }}
    {{ block('threadList') }}
    {{ block('actionbar') }}
    <hr />
    {{ block('help_block') }}
{% endblock %}
