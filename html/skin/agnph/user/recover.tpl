{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/recover-style.css" />
{% endblock %}

{% block scripts %}
{% endblock %}

{% block content %}
    <div class="recover-form">
        <h4>Account Recovery</h4>
        <p>
            Please confirm your email address to change your password.
        </p>
        {% if error %}
            <p>
                <span class="error">{{ error }}</span>
            </p>
        {% endif %}
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
