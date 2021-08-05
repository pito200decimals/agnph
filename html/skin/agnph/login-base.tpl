{% extends "base.tpl" %}

{% block styles %}
<style>
    #login-box {
        width: 320px;
    }
    #forgot-password {
        font-size: 75%;
    }
    .label {
        text-align: right;
    }
    #old-account-note {
        margin: auto;
        display: block;
        text-align: center;
        width: 60%;
    }
</style>
{% endblock %}

{% block content %}
    <div class="not-font-scalable">
        {% if login_msg|length > 0 %}
            <div id="old-account-note">
                {% autoescape false %}
                    {{ login_msg }}
                {% endautoescape %}
            </div>
        {% endif %}
        <div class="form-box" id="login-box">
            <h3>Login</h3>
            {{ block('banner') }}
            <p>
                <form action="/login/" method="POST" accept-charset="UTF-8">
                    <table>
                        <tbody>
                            <tr><td class="label"><label>Username/Email:</label></td><td><input type="text" name="username" value="{{ username }}" required /></td></tr>
                            <tr><td class="label"><label>Password:</label></td><td><input type="password" name="password" value="" required{% if username %}{{ " " }}autofocus{% endif %}/></td></tr>
                            <tr><td></td><td><input type="submit" value="Login" /></td></tr>
                            <tr><td></td><td><a id="forgot-password" href="/recover/">Forgot your password?</a></td></tr>
                        </tbody>
                    </table>
                </form>
            </p>
        </div>
    </div>
{% endblock %}
