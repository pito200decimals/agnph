{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/mail-style.css" />
{% endblock %}

{% block scripts %}
{% endblock %}

{#
TODO: Sidebar for user gallery actions.
{% block sidebar %}
{% endblock %}
#}

{% block usercontent %}
    <div>
        <h3>Messages</h3>
        {# Display message list. #}
        <table class="message-table">
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
                            <td><div>{% if message.inbox %}INBOX{% elseif message.outbox %}OUTBOX{% endif %}</div></td>
                            <td><div>{{ message.date }}</div></td>
                            <td><div><a href="/mail/message/{{ message.Id }}/">{{ message.Title }}</a></div></td>
                            <td><div><a href="/user/{{ message.toFromUser.UserId }}/">{{ message.toFromUser.DisplayName }}</a></div></td>
                            <td><div><input type="checkbox" name="" /></div></td>
                        </tr>
                    {% endfor %}
                {% else %}
                    <tr class="no-messages">
                        <td colspan="5">
                            <div>
                                {# No posts here. #}
                                No messages found.
                            </div>
                        </td>
                    </tr>
                {% endif %}
            </tbody>
        </table>
        <div class="Clear">&nbsp;</div>
        <div class="indexIterator">
            {% autoescape false %}
            {{ postIterator }}
            {% endautoescape %}
        </div>
    </div>
{% endblock %}
