{% extends "base.tpl" %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
    {% endif %}
{% endblock %}

{% block styles %}
    {{ parent() }}
    <style>
        #new-user-box {
            max-width: 480px;
        }
        #new-user-box li {
            margin-top: 10px;
        }
    </style>
{% endblock %}

{% block content %}
    <div class="form-box" id="new-user-box">
        <p>This is the first time you've logged in since the site changed its software. Please take the time to ensure that your <a href="/user/{{ user.UserId }}/">email address and other account information</a> are correct.</p>
        <p>It is important that your email address is correct, otherwise you will be <strong>unable to recover or change your password</strong>.</p>
        <p>If you had registered other accounts previously on different AGNPH sections:</p>
        <ul>
            <li>With the same username and email: They have been merged into this account automatically.</li>
            <li>With a different uername or email: <strong>Do not</strong> log directly into them. Instead, you can merge them into this account in your <a href="/user/account/link/">account settings</a>.</li>
        </ul>
        <p><a href="/">Click here to continue</a></p>
    </div>
{% endblock %}
