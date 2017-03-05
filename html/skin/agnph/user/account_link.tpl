{% extends 'base.tpl' %}

{% block styles %}
    {{ parent() }}
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
    {{ parent() }}
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
        In late 2015, AGNPH moved to a new software system. Although all old site accounts were
        retained, some users may have accidentally created new accounts on the new site
        unknowingly, or may have had multiple previous accounts for different sections on the old
        site.
    </p>
    <p>
        You can use this page to import your old site account(s). This will merge all content
        associated with that account into your currently-logged-in-account, and delete the old
        username.
    </p>
    {% if similar_accounts %}
        <p>
            Potential accounts detected: <strong>{{ similar_accounts }}</strong>
        </p>
    {% endif %}
    <p>&nbsp;</p>
    <div class="step">
        <p>
            Select the service your old account was signed up for:
        </p>
        <input id="type-forums" class="service-select" type="radio" name="service" value="forums" /><label for="type-forums">Forums</label><br />
        <input id="type-gallery" class="service-select" type="radio" name="service" value="gallery" /><label for="type-gallery">Gallery</label><br />
        <input id="type-fics" class="service-select" type="radio" name="service" value="fics" /><label for="type-fics">Fics</label><br />
        <input id="type-oekaki" class="service-select" type="radio" name="service" value="oekaki" /><label for="type-oekaki">Oekaki</label><br />
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
    <div id="step-2-oekaki" class="step step-2">
        <p>
            Enter your oekaki username and password:
        </p>
        <form method="POST" accept-encoding="UTF-8">
            <input type="hidden" name="service" value="oekaki" />
            <label>Username:</label><input type="text" name="oekaki-username" value="" /><br />
            <label>Password:</label><input type="password" name="oekaki-password" value="" /><br />
            <input type="submit" value="Recover" />
        </form>
    </div>
{% endblock %}
