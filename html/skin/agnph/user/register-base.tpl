{% extends 'base.tpl' %}

{% block styles %}
    <style>
        #register-form {
            max-width: 800px;
        }
        #register-form ul {
            list-style: none;
            padding: 0px;
        }
        #register-form li {
            margin: 5px;
        }
        #register-form li label {
            display: inline-block;
            width: 200px;
        }
        .captcha-offset {
            margin-left: 200px;
        }
        .form-error {
            display: none;
            color: red;
            margin-left: 5px;
            font-size: 75%;
        }
        #register-disclaimer {
            margin: 5px;
            font-size: 75%;
        }
        @media only handheld, screen and (max-device-width: 500px), screen and (max-width: 500px) {
            .captcha-offset {
                margin-left: 0px;
            }
        }
    </style>
{% endblock %}

{% block scripts %}
    {{ parent() }}
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
                var age_check = $("#age-check").is(":checked");
                var rules_check = $("#rules-check").is(":checked");
                var terms_check = $("#terms-check").is(":checked");
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
                if (email.includes("@outlook.com") || email.includes("@hotmail.com")) {
                    $("#email-domain-error").show();
                } else {
                    $("#email-domain-error").hide();
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
                if (!age_check) {
                    err = true;
                }
                if (!rules_check) {
                    err = true;
                }
                if (!terms_check) {
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
            $("input").change(check);
            check();
        });
    </script>
{% endblock %}

{% block content %}
    <div id="register-form" class="form-box">
        <h3>Register Account</h3>
        {{ block('banner') }}
        <form action="" method="POST" accept-charset="UTF-8">
            <ul>
                <li>
                    <label>Username:</label><input id="username" name="username" type="text" value="{{ username }}" autofocus="autofocus" /><span id="name-error" class="form-error">Must use characters a-z, 0-9, _</span><span id="name-length-error" class="form-error">Username must be between 3 and 12 characters.</span>
                </li>
                <li>
                    <label>Email:</label><input id="email" name="email" type="text" value="{{ email }}" />
                    <p id="email-domain-error" class="form-error">We recommend <strong>against</strong> using an email account from @hotmail.com or @outlook.com since Microsoft will likely block your registration email (we're trying to fix this).</p>
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
                    <label>Birthday:</label><input id="bday" name="bday" type="date" value="{{ bday }}" min="{{ min_bday }}" max="{{ max_bday }}" /><span id="bday-error"></span>
                </li>
                <li>
                    <img class="captcha-offset" id="captcha-img" src="/register/captcha/" alt="Captcha text" /><br />
                    <small class="captcha-offset"><a href="javascript: refresh()" tabindex="-1">Can't read?</a></small><br />
                    <label>Enter Captcha:</label><input id="captcha" name="captcha" type="text" value="" />
                </li>
            </ul>
            <div>
                <input type="checkbox" name="rules-check" id="rules-check"><label for="rules-check">I have read the <a href="/about/rules/">Site Rules</a></label>
            </div>
            <div>
                <input type="checkbox" name="terms-check" id="terms-check"><label for="terms-check">I agree to the <a href="/about/privacy/">Site Privacy Policy</a></label>
            </div>
            <div>
                <input type="checkbox" name="age-check" id="age-check"><label for="age-check">I am 18 years or older</label>
            </div>
            <div id="register-disclaimer">
                {% autoescape false %}
                    {{ registerDisclaimerMessage }}
                {% endautoescape %}
            </div>
            <input id="button" type="submit" value="Register" />
        </form>
    </div>
{% endblock %}
