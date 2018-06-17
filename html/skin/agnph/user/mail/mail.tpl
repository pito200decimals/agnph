{% extends "user/skin-base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/user/mail-style.css') }}" />
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script type="text/javascript">
        $(document).ready(function() {
            $('ul.tabs li').click(function() {
                var tab_id = $(this).attr('data-tab');
                var hash = tab_id.substring(4);
                location.hash = '#' + hash;
            });
            onHashChange();
        });
        $(window).on('hashchange', onHashChange);
        function onHashChange() {
            var tab_id = 'tab-messages';
            if (location.hash.length > 0) {
                tab_id = "tab-" + location.hash.substring(1);
            }
            var tab = $('[data-tab="' + tab_id + '"]');
            if (tab.length == 0) {
                tab = $('[data-tab="tab-messages"]');
            }
            if (tab.length > 0) {
                var tab_id = tab.attr('data-tab');
                var content = $('#' + tab_id);
                if (content.length > 0) {
                    $('ul.tabs li').removeClass('current');
                    $('.tab-content').removeClass('current');
                    tab.addClass('current');
                    content.addClass('current');
                }
            }
        }
    </script>
{% endblock %}

{% block sidebar %}
    <h4>Actions</h4>
    <ul>
        <li><a href="/user/{{ profile.user.UserId }}/mail/compose/">Compose Message</a></li>
    </ul>
{% endblock %}

{% block usercontent %}
    <div class="comments">
        {# Top-level tabs #}
        <a id="reviews"></a>
        <ul class="tabs">
            <li class="tab-link current" data-tab="tab-messages">Private Messages{% if unread_message_count > 0 %} <span class="unread-messages">({{ unread_message_count }})</span>{% endif %}</li>
            <li class="tab-link" data-tab="tab-notifications">Notifications{% if unread_notification_count > 0 %} <span class="unread-messages">({{ unread_notification_count }})</span>{% endif %}</li>
        </ul>

        {# Pane for private messages #}
        <div id="tab-messages" class="tab-content current">
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
                                <td><div>
                                    {% if message.inbox %}
                                        <img class="mail-icon" src="/images/inbox_icon.png" />
                                    {% elseif message.outbox %}
                                        <img class="mail-icon" src="/images/outbox_icon.png" />
                                    {% elseif message.notification %}
                                        <img class="mail-icon" src="/images/favicon.png" />
                                    {% endif %}
                                    {% if message.count > 1 %}({{ message.count }}){% endif %}
                                </div></td>
                                <td><div>{{ message.date }}</div></td>
                                <td><div><a href="/user/{{ profile.user.UserId }}/mail/message/{{ message.Id }}/">{{ message.Title }}</a></div></td>
                                <td><div><a href="/user/{{ message.toFromUser.UserId }}/">{{ message.toFromUser.DisplayName }}</a></div></td>
                            </tr>
                        {% endfor %}
                    {% else %}
                        <tr>
                            <td></td>
                            <td colspan="4">No messages found</td>
                        </tr>
                    {% endif %}
                </tbody>
            </table>
            <div class="Clear">&nbsp;</div>
            <div class="iterator">
                {% autoescape false %}{{ mail_iterator }}{% endautoescape %}
            </div>
        </div>

        {# Pane for notifications #}
        <div id="tab-notifications" class="tab-content">
            {# Display notifications list. #}
            <table class="list-table">
                <thead>
                    <tr>
                        <td><div><strong>Subject</strong></div></td>
                    </tr>
                </thead>
                <tbody>
                    {% if notifications|length > 0 %}
                        {% for notification in notifications %}
                            <tr class="{% if notification.Status == 'U' %}unread{% endif %}">
                                <td><div><a href="/user/{{ profile.user.UserId }}/mail/message/{{ notification.Id }}/">{{ notification.Title }}</a></div></td>
                            </tr>
                        {% endfor %}
                    {% else %}
                        <tr>
                            <td colspan="2">No notifications found</td>
                        </tr>
                    {% endif %}
                </tbody>
            </table>
            <div class="Clear">&nbsp;</div>
            <div class="iterator">
                {% autoescape false %}{{ notification_iterator }}{% endautoescape %}
            </div>
        </div>
    </div>
{% endblock %}
