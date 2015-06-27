{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/mail-style.css" />
{% endblock %}

{% block scripts %}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
{% endblock %}

{#
TODO: Sidebar for user gallery actions.
{% block sidebar %}
{% endblock %}
#}

{% block usercontent %}
    <div>
        <h3>Messages</h3>
        {% if action %}
            <div class="completed-action-box">
                <p>
                    <strong>{{ action }}</strong>
                    <input type="button"onclick="$('.completed-action-box').hide();" value="X" />
                </p>
            </div>
        {% endif %}
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
                            <td><div>{% if message.inbox %}INBOX_ICON{% elseif message.outbox %}OUTBOX_ICON{% endif %} {% if message.count > 1 %}({{ message.count }}){% endif %}</div></td>
                            <td><div>{{ message.date }}</div></td>
                            <td><div><a href="/user/{{ user.UserId }}/mail/message/{{ message.Id }}/">{{ message.Title }}</a></div></td>
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
        {% if iterator %}
            <div class="indexIterator">
                {% autoescape false %}
                {{ iterator }}
                {% endautoescape %}
            </div>
        {% endif %}
    </div>
{% endblock %}
