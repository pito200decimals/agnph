{% extends "base.tpl" %}

{% block styles %}
<link rel="stylesheet" type="text/css" href="{{ skinDir }}/login-style.css" />
{% endblock %}

{% block content %}
<div class="loginbox">
    <div>
        <h4>Login</h4>
        {% if error %}
            <p>
                <span class="error">{{ error }}</span>
            </p>
        {% endif %}
        <p>
            <form action="/login/" method="POST" accept-charset="UTF-8">
                <table>
                    <tbody>
                        <tr><td><label>Username:</label></td><td><input type="text" name="username" value="{{ username }}" required /></td></tr>
                        <tr><td><label>Password:</label></td><td><input type="password" name="password" value="" required {% if username %}autofocus {% endif %}/></td></tr>
                        <tr><td></td><td><input type="submit" value="Login" /></td></tr>
                    </tbody>
                </table>
            </form>
        </p>
    </div>
</div>
{% endblock %}
