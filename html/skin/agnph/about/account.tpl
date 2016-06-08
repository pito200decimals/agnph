{% extends "about/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
{% endblock %}

{% block content %}
    <h3>AGNPH Help Pages (Accounts)</h3>
    <div class="block">
        <div class="header">Help! I had an account and I forgot my password!</div>
        <div class="content">
            <p>
                If you registered an account after the site remake, you should be able to have your password reset via email by clicking "Forgot your password?" at the login screen.
            </p>
            <p>
                If you registered your account <strong>before</strong> the site remake and can't remember your password, you should:
            </p>
            <ol>
                <li>Register a new account. You can use the same username and/or email as your old account if you want to.</li>
                <li>Contact a staff administrator either via PM, via IRC, or via another site like FA. See the <a href="/about/staff/">staff page</a> for info about staff.</li>
                <li>Once an administrator has verified your identity, they can reset the password on your old inactive account. You can then follow the instructions for multiple accounts below.</li>
            </ol>
        </div>
    </div>
    <div class="block">
        <div class="header">I had multiple accounts before the site remake. What do I do?</div>
        <div class="content">
            <p>
                If you had multiple accounts before the site remake, please <strong>do not log into all of them</strong>. Instead, either register a new primary account, or log into <strong>only</strong>
                the primary account you'd like to keep.
            </p>
            <p>
                Then, under your account settings in the sidebar, you can import/link your other accounts. This will merge it and all content associated with it into your current primary account.
            </p>
        </div>
    </div>
{% endblock %}
