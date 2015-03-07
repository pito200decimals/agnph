{% extends 'base.tpl' %}

{% block styles %}
<link rel="stylesheet" type="text/css" href="{{ skinDir }}/forums/style.css" />
{% endblock %}

{% block content %}
{#
    If main lobby page, will contain a variable called "home" = array of lobbies. Each lobby has a field called "childBoards", which is another array of lobbies.
    If sublobby board, will contain a variable called "board". This will contain a field called "threads" which is an array of posts (threads) for the given page.
#}
    {% if home %}
        {% include 'forums/viewboard-root.tpl' %}
    {% elseif board %}
        {% include 'forums/viewboard-board.tpl' %}
    {% elseif content %}
        {{ content }}
    {% else %}
        No content here.
    {% endif %}
{% endblock %}
