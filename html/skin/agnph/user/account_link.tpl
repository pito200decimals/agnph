{% extends 'base.tpl' %}

{% block styles %}
    <style>
        .step {
            margin: 15px;
        }
        .step-2 {
            display: none;
        }
        label {
            display: inline-block;
            min-width: 100px;
        }
    </style>
{% endblock %}

{% block scripts %}
    <script>
        $(document).ready(function() {
            $(".service-select").click(function() {
                $(".step-2").hide();
                $("#step-2-" + $(this).val()).show();
            });
        });
    </script>
{% endblock %}

{% block content %}
    <h3>Account Recovery Console</h3>
    {{ block('banner') }}
    <p>
        This console will allow you to combine your old, imported AGNPH account with your current new account.
    </p>
    <div class="step">
        <p>
            Select the service your original account was signed up for:
        </p>
        <input class="service-select" type="radio" name="service" value="forums" /> Forums<br />
        <input class="service-select" type="radio" name="service" value="gallery" disabled /> Gallery<br />
        <input class="service-select" type="radio" name="service" value="fics" /> Fics<br />
        <input class="service-select" type="radio" name="service" value="oekaki" disabled /> Oekaki<br />
    </div>
    <div id="step-2-forums" class="step step-2">
        <p>
            Enter your forums username and password:
        </p>
        <form method="POST" accept-encoding="UTF-8">
            <input type="hidden" name="service" value="forums" />
            <label>Username:</label><input type="text" name="forums-username" value="" /><br />
            <label>Password:</label><input type="password" name="forums-password" value="" /><br />
            <input type="submit" value="Recover" />
        </form>
    </div>
    <div id="step-2-gallery" class="step step-2">
        <p>
            Enter your gallery username and password:
        </p>
        <form method="POST" accept-encoding="UTF-8">
            <input type="hidden" name="service" value="gallery" />
            <label>Username:</label><input type="text" name="gallery-username" value="" /><br />
            <label>Password:</label><input type="password" name="gallery-password" value="" /><br />
            <input type="submit" value="Recover" />
        </form>
    </div>
    <div id="step-2-fics" class="step step-2">
        <p>
            Enter your fics username and password:
        </p>
        <form method="POST" accept-encoding="UTF-8">
            <input type="hidden" name="service" value="fics" />
            <label>Username:</label><input type="text" name="fics-username" value="" /><br />
            <label>Password:</label><input type="password" name="fics-password" value="" /><br />
            <input type="submit" value="Recover" />
        </form>
    </div>
{% endblock %}
