{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/list-style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/mail-style.css" />
{% endblock %}

{% block scripts %}
{% endblock %}

{% block sidebar %}
    <h4>Actions</h4>
    <ul>
        <li><a href="/user/{{ profile.user.UserId }}/mail/compose/">Compose Message</a></li>
    </ul>
{% endblock %}

{% block usercontent %}
    <div>
        <h3>Messages</h3>
        {{ block('banner') }}
        {# Display message list. #}
        <table class="list-table">
            <thead>
                <tr>
                    <td><div>{# Send/Recv column #}&nbsp;</div></td>
                    <td><div><strong>Date</strong></div></td>
                    <td><div><strong>Subject</strong></div></td>
                    <td><div><strong>To/From</strong></div></td>
                    <td><div><input name="select-all" type="checkbox" /></div></td>
                </tr>
            </thead>
            <tbody>
                {% if messages|length > 0 %}
                    {% for message in messages %}
                        <tr class="{% if message.Status == 'U' %}unread{% endif %}">
                            <td><div>{% if message.inbox %}INBOX_ICON{% elseif message.outbox %}OUTBOX_ICON{% endif %} {% if message.count > 1 %}({{ message.count }}){% endif %}</div></td>
                            <td><div>{{ message.date }}</div></td>
                            <td><div><a href="/user/{{ profile.user.UserId }}/mail/message/{{ message.Id }}/">{{ message.Title }}</a></div></td>
                            <td><div><a href="/user/{{ message.toFromUser.UserId }}/">{{ message.toFromUser.DisplayName }}</a></div></td>
                            <td><div><input type="checkbox" name="" /></div></td>
                        </tr>
                    {% endfor %}
                {% else %}
                    <tr>
                        <td colspan="5">No messages found</td>
                    </tr>
                {% endif %}
            </tbody>
        </table>
        <div class="Clear">&nbsp;</div>
        {% if iterator %}
            <div class="iterator">
                {% autoescape false %}
                {{ iterator }}
                {% endautoescape %}
            </div>
        {% endif %}
    </div>
{% endblock %}
