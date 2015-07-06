{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/register-style.css" />
{% endblock %}

{% block scripts %}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script type="text/javascript">
        function refresh() {
            $('#captcha-img').attr("src", "/register/captcha/?timestamp=" + new Date().getTime());
        }
        $(document).ready(function() {
            function check() {
                var err = false;
                var name = $("#username").val().toLowerCase();
                var email = $("#email").val();
                var email_confirm = $("#email-confirm").val();
                var password = $("#password").val();
                var password_confirm = $("#password-confirm").val();
                var bday = $("#bday").val();
                var captcha = $("#captcha").val();
                if (name.length == 0) {
                    err = true;
                    $("#name-error").hide();
                    $("#name-length-error").hide();
                } else if (!(/^([a-zA-Z0-9_]+)$/.test(name))) {
                    err = true;
                    $("#name-error").show();
                    $("#name-length-error").hide();
                } else if (name.length < 3 || name.length > 24) {
                    err = true;
                    $("#name-error").hide();
                    $("#name-length-error").show();
                } else {
                    $("#name-error").hide();
                    $("#name-length-error").hide();
                }
                {# Note: Don't show email regex check to client #}
                if (email.length == 0) {
                    err = true;
                }
                if (email_confirm.length == 0) {
                    err = true;
                    $("#email-match-error").hide();
                } else if (email != email_confirm) {
                    err = true;
                    $("#email-match-error").show();
                } else {
                    $("#email-match-error").hide();
                }
                if (password.length == 0) {
                    err = true;
                    $("#pass-length-error").hide();
                } else if (password.length > 0 && password.length < 4) {
                    err = true;
                    $("#pass-length-error").show();
                } else {
                    $("#pass-length-error").hide();
                }
                if (password_confirm.length == 0) {
                    err = true;
                    $("#pass-error").hide();
                } else if (password != password_confirm) {
                    err = true;
                    $("#pass-error").show();
                } else {
                    $("#pass-error").hide();
                }
                if (bday.length == 0) {
                    {# TODO: Do some validation on birthday #}
                    err = true;
                }
                if (captcha.length == 0) {
                    err = true;
                }
                if (err) {
                    $("#button").prop("disabled", true);
                } else {
                    $("#button").prop("disabled", false);
                }
            }
            $("input").keyup(check);
            $("input").focusout(check);
            check();
        });
    </script>
{% endblock %}

{% block content %}
    <div class="register-form">
        <h3>Register Account</h3>
        {{ block('banner') }}
        <form action="" method="POST" accept-charset="UTF-8">
            <ul>
                <li>
                    <label>Username:</label><input id="username" name="username" type="text" value="{{ username }}" autofocus="autofocus" /><span id="name-error" class="form-error">Must use characters a-z, 0-9, _</span><span id="name-length-error" class="form-error">Username must be between 3 and 12 characters.</span>
                </li>
                <li>
                    <label>Email:</label><input id="email" name="email" type="text" value="{{ email }}" />
                </li>
                <li>
                    <label>Repeat Email:</label><input id="email-confirm" name="email-confirm" type="text" value="{{ email }}" /><span id="email-match-error" class="form-error">Emails must match</span>
                </li>
                <li>
                    <label>Password:</label><input id="password" name="password" type="password" value="" /><span id="pass-length-error" class="form-error">Password must be at least 4 characters</span>
                </li>
                <li>
                    <label>Repeat Password:</label><input id="password-confirm" name="password-confirm" type="password" value="" /><span id="pass-error" class="form-error">Passwords must match</span>
                </li>
                <li>
                    <label>Birthday:</label><input id="bday" name="bday" type="date" value="{{ bday }}" /><span id="bday-error"></span>
                </li>
                <li>
                    <img class="captcha-offset" id="captcha-img" src="/register/captcha/" alt="Captcha text" /><br />
                    <small class="captcha-offset"><a href="javascript: refresh()" tabindex="-1">Refresh image</a></small><br />
                    <label>Enter Captcha:</label><input id="captcha" name="captcha" type="text" value="" />
                </li>
            </ul>
            <div class="register-disclaimer">
                {% autoescape false %}
                    {{ RegisterDisclaimer }}
                {% endautoescape %}
            </div>
            <input id="button" type="submit" value="Register" />
        </form>
    </div>
{% endblock %}