{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/mail-style.css" />
    <link rel="stylesheet" type="text/css" href="/skin/agnph/comments-style.css" />
{% endblock %}

{% block scripts %}
    {% if canSendPM %}
        <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
        <script type="text/javascript">
            tinymce.init({
                selector: "textarea#reply-to",
                plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
                target_list: [ {title: 'New page', value: '_blank'} ],
                toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
                contextmenu: "image link | hr",
                autoresize_max_height: 200,
                resize: false,
                menubar: false
            });
        </script>
    {% endif %}
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
        {% if canSendPM %}
            <form action="/user/{{ profile.user.UserId }}/mail/send/" method="POST" accept-charset="UTF-8">
                <input type="hidden" name="rid" value="{{ rid }}" />
                <textarea id="reply-to" name="message">
                    {% autoescape false %}
                        {{ profile.user.bio }}
                    {% endautoescape %}
                </textarea>
                <input type="submit" value="Reply" />
            </form>
        {% endif %}
        <ul class="comment-list">
            {% for comment in messages %}
                {{ block('comment') }}
                {#{{ block('message_block') }}#}
            {% endfor %}
        </ul>
    </div>
{% endblock %}
