{% extends "base.tpl" %}

{% block styles %}
    {{ parent() }}
    <style>
        .center {
            text-align: center;
            margin: 50px;
        }
        .center a {
            margin: 20px;
        }
        .center a:hover {
            text-shadow: 1px 1px 1px rgb(0,0,127);
            text-decoration: none;
        }
        .confirm-link {
            display: inline-block;
        }
    </style>
{% endblock %}
{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
    {% endif %}
    <script>
        $(document).ready(function() {
            $(".confirm-link a").click(function(e) {
                setCookie("confirmed_age", "true");
                location.reload();
                return false;
            });
        });
    </script>
{% endblock %}

{% block content %}
    <div class="center">
        <p>
            Warning: You must be at least 18 years of age to view this page.
        </p>
        <div>
            <div class="confirm-link"><a href="#">Yes, let me in!</a></div><div class="confirm-link"><a href="http://www.pokemon.com/">No, get me out of here!</a></div>
        </div>
    </div>
{% endblock %}
