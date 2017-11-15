{% extends 'base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/user/recover-style.css') }}" />
    <style>
        h4 {
            text-align: center;
        }
        #recover-form {
            max-width: 400px;
        }
        #old-account-note {
            margin: auto;
            display: block;
            text-align: center;
            width: 60%;
        }
    </style>
{% endblock %}

{% block scripts %}
    {{ parent() }}
{% endblock %}

{% block content %}
    <div id="old-account-note">
        <p>
            <strong>If you had an account in 2015 or prior and have not logged into it since the site update, you cannot recover your password via this form.</strong> Please register a new account, then contact an administrator to import all of your old data.
        </p>
    </div>
    <div id="recover-form" class="form-box">
        <h4>Account Recovery</h4>
        {{ block('banner') }}
        <p>
            Please confirm your email address to change your password.
        </p>
        <p>
            <form action="/recover/" method="POST" accept-charset="UTF-8">
                <table>
                    <tbody>
                        <tr><td class="label"><label>Email Address:</label></td><td><input type="text" name="email" value="{{ email }}" required /></td></tr>
                        <tr><td class="label"><label>New Password:</label></td><td><input type="password" name="password" value="" required /></td></tr>
                        <tr><td class="label"><label>Repeat Password:</label></td><td><input type="password" name="password-confirm" value="" required /></td></tr>
                        <tr><td></td><td><input type="submit" value="Recover Account" /></td></tr>
                    </tbody>
                </table>
            </form>
        </p>
    </div>
{% endblock %}
