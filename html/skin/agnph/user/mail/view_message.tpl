{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/mail-style.css" />
    <link rel="stylesheet" type="text/css" href="/skin/agnph/comments-style.css" />
{% endblock %}

{% block scripts %}
{% endblock %}

{#
TODO: Sidebar for user gallery actions.
{% block sidebar %}
{% endblock %}
#}

{% use 'includes/comment-block.tpl' %}

{% block message_block %}
    <div class="pm-block">
        <div>
            <strong>{{ message.Title }}</strong>
            {{ message.date }}
        </div>
        <div>
            {% autoescape false %}
                {{ message.Content }}
            {% endautoescape %}
        </div>
    </div>
{% endblock %}

{% block usercontent %}
    <div>
        {% if user.GroupMailboxThreads %}
            <h3>Conversation with {{ message.toFromUser.DisplayName }}</h3>
        {% elseif message.inbox %}
            <h3>Message from {{ message.toFromUser.DisplayName }}</h3>
        {% else %}
            <h3>Message to {{ message.toFromUser.DisplayName }}</h3>
        {% endif %}
        <ul class="comment-list">
            {% for comment in messages %}
                {{ block('comment') }}
                {#{{ block('message_block') }}#}
            {% endfor %}
        </ul>
    </div>
{% endblock %}
