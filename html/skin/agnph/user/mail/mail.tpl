{% extends "user/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/user/mail-style.css') }}" />
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}

{% block sidebar %}
    <h4>Actions</h4>
    <ul>
        <li><a href="/user/{{ profile.user.UserId }}/mail/compose/">Compose Message</a></li>
    </ul>
{% endblock %}

{% block usercontent %}
    <h3>Messages</h3>
    {# Display message list. #}
    <div class="action-bar">
        <form id="mark-read-form" method="POST" accept-encoding="UTF-8">
            <input type="hidden" name="action" value="mark-all-read" />
        </form>
        <a href="" onclick="document.getElementById('mark-read-form').submit();return false;">Mark All as Read</a>
    </div>
    <table class="list-table">
        <thead>
            <tr>
                <td><div>{# Send/Recv column #}&nbsp;</div></td>
                <td><div><strong>Date</strong></div></td>
                <td><div><strong>Subject</strong></div></td>
                <td><div><strong>To/From</strong></div></td>
            </tr>
        </thead>
        <tbody>
            {% if messages|length > 0 %}
                {% for message in messages %}
                    <tr class="{% if message.Status == 'U' %}unread{% endif %}">
                        <td><div>{% if message.inbox %}<img class="mail-icon" src="/images/inbox_icon.png" />{% elseif message.outbox %}<img class="mail-icon" src="/images/outbox_icon.png" />{% endif %} {% if message.count > 1 %}({{ message.count }}){% endif %}</div></td>
                        <td><div>{{ message.date }}</div></td>
                        <td><div><a href="/user/{{ profile.user.UserId }}/mail/message/{{ message.Id }}/">{{ message.Title }}</a></div></td>
                        <td><div><a href="/user/{{ message.toFromUser.UserId }}/">{{ message.toFromUser.DisplayName }}</a></div></td>
                    </tr>
                {% endfor %}
            {% else %}
                <tr>
                    <td colspan="1">No messages found</td>
                    <td colspan="4"></td>
                </tr>
            {% endif %}
        </tbody>
    </table>
    <div class="Clear">&nbsp;</div>
    <div class="iterator">
        {% autoescape false %}{{ iterator }}{% endautoescape %}
    </div>
{% endblock %}
