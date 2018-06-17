{% extends "user/skin-base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/user/mail-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="/skin/agnph/comments-style.css" />
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
        function MarkAllRead() {
            $('#mark-read-form').submit();
            return false;
        }
        function DeleteNotification(id) {
            $('[name="notification-id"]').val(id);
            $('#delete-notification').submit();
            return false;
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
                    <input type="hidden" name="hash" value="#messages" />
                </form>
                <a href="" onclick="return MarkAllRead()">Mark All as Read</a>
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
            <form id="delete-notification" method="POST" accept-encoding="UTF-8">
                <input type="hidden" name="action" value="delete-notification" />
                <input type="hidden" name="notification-id" value="" />
                <input type="hidden" name="hash" value="#notifications" />
            </form>
            <ul class="comment-list">
                {% for notification in notifications %}
                    {{ block('notification') }}
                {% endfor %}
            </ul>
            <div class="Clear">&nbsp;</div>
            <div class="iterator">
                {% autoescape false %}{{ notification_iterator }}{% endautoescape %}
            </div>
        </div>
    </div>
{% endblock %}

{% block notification %}
    <li class="comment">
        {% if notification.SenderUserId > 0 %}
            <div class="comment-side-panel">
                <div>
                    <a href="/user/{{ notification.user.UserId }}/">
                        <img class="comment-avatarimg" src="{{ notification.user.avatarURL }}" />
                    </a>
                    <div class="Clear">&nbsp;</div>
                </div>
                {% if notification.user.Title|length > 0 %}
                    <span class="comment-side-panel-label">{{ notification.user.Title }}</span>
                {% endif %}
            </div>
        {% endif %}
        <div class="comment-content">
            <div class="commentheader">
                <div class="edit-comment-form"><a href="" onclick="return DeleteNotification({{ notification.Id }})">Delete</a></div>
                <strong>{{ notification.title }}</strong><br />
                <span>Date: {{ notification.date }}</span>
            </div>
            <div class="commenttext">
                {% autoescape false %}
                    {{ notification.text }}
                {% endautoescape %}
            </div>
        </div>
        <div class="Clear">&nbsp;</div>
        {% if notification.anchor %}
            <div><small class="direct-link"><a href="#{{ notification.anchor }}">Link to post</a></small></div>
            <div class="Clear">&nbsp;</div>
        {% endif %}
    </li>
{% endblock %}
