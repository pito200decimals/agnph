{% extends "user/skin-base.tpl" %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/user/mail-style.css')|raw }}
    {{ inline_css_asset('/comments-style.css')|raw }}
{% endblock %}

{% block profile_sidepanel %}
    <div class="sidepanel-section">
        <img class="profile-avatarimg" src="{{ message.toFromUser.avatarURL }}" />
    </div>
{% endblock %}

{% block scripts %}
    {{ parent() }}
    {% if canSendPM %}
        <script src="{{ asset('/scripts/tinymce.min.js') }}"></script>
        <script type="text/javascript">
            tinymce.init({
                selector: "textarea#reply-to",
                plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
                target_list: [ {title: 'New page', value: '_blank'} ],
                toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
                contextmenu: "image link | hr",
                autoresize_max_height: 300,
                resize: true,
                menubar: false,
                relative_urls: false
            });
        </script>
    {% endif %}
{% endblock %}

{% use 'includes/comment-block.tpl' %}

{% block usercontent %}
    {% if message.MessageType == 0 %}
        {% if user.GroupMailboxThreads %}
            <h3>Conversation with{{ " " }}{{ message.toFromUser.DisplayName }}</h3>
        {% elseif message.inbox %}
            <h3>Message from{{ " " }}{{ message.toFromUser.DisplayName }}</h3>
        {% else %}
            <h3>Message to{{ " " }}{{ message.toFromUser.DisplayName }}</h3>
        {% endif %}
    {% endif %}
    {% if canSendPM %}
        <form action="/user/{{ profile.user.UserId }}/mail/send/" method="POST" accept-charset="UTF-8">
            <input type="hidden" name="rid" value="{{ rid }}" />
            <textarea id="reply-to" name="message">
            </textarea>
            <input type="submit" value="Reply" />
        </form>
    {% endif %}
    <ul class="comment-list">
        {% for comment in messages %}
            {{ block('comment') }}
        {% endfor %}
    </ul>
{% endblock %}
