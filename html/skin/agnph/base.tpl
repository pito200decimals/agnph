{% spaceless %}
{# Define blocks to be used elsewhere #}
{% if false %}
    {# Notification banners #}
    {% block banner %}
        {% for notification in banner_notifications %}
            <div class="banner-nofication{% for class in notification.classes %} {{ class }}{% endfor %}">
                <p>
                    {% if notification.strong %}<strong>{% endif %}
                        {% if notification.noescape %}
                            {% autoescape false %}{{ notification.text }}{% endautoescape %}
                        {% else %}
                            {{ notification.text }}
                        {% endif %}
                    {% if notification.strong %}</strong>{% endif %}
                    {% if notification.dismissable %}
                        <input type="button"onclick="$(this).parent().parent().hide();" value="X" />
                    {% endif %}
                </p>
            </div>
        {% endfor %}
    {% endblock %}
{% endif %}

{# Main site base template #}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {% block head %}
            <title>AGNPH</title>
        {% endblock %}
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="{{ skinDir }}/base.js"></script>
        {% block scripts %}
        {% endblock %}
        <link rel="stylesheet" type="text/css" href="{{ skinDir }}/style.css" />
        {% block styles %}
        {% endblock %}
        {% if debug %}{# TODO: Remove #}
            <style type="text/css">
                div,span,li {
                    border-style: dotted;
                    border-width: 1px;
                }
            </style>
        {% endif %}
    </head>
    <body>
        <div class="navigation">
            {% block main_navigation %}
                {# float right goes before normal nav, so it positions correctly #}
                <ul class="navigation_right">
                    {% if user %}
                        <noscript><li><a href="/logout/">Log out</a></li></noscript>
                        {# TODO: Mail notifications #}
                        <li><a href="/user/{{ user.UserId }}/mail/"><img src="/images/message.png" /></a></li>
                        <li id="account_link">
                            <a href="/user/{{ user.UserId }}/"><img src="{{ user.avatarURL }}" />{{ user.DisplayName }}</a>
                            <ul id="account_dropdown" class="navigation account_nav_dropdown" hidden>
                                <li><a href="/user/{{ user.UserId }}/">Profile</a></li>
                                <li><a href="/user/{{ user.UserId }}/preferences/">Settings</a></li>
                                <li><a href="/logout/">Log out</a></li>
                            </ul>
                        </li>
                    {% else %}
                        <li><a href="/login/">Login</a></li>
                        <li><a href="/register/">Register</a></li>
                    {% endif %}
                </ul>
                <img id="main_menu_icon" src="/images/menu-icon.png" />
                <ul class="navigation_left">
                    <li{% if nav_section=="home" %} class="selected-nav"{% endif %}><a href="/">Home</a></li>
                    <li{% if nav_section=="forums" %} class="selected-nav"{% endif %}><a href="/forums/">Forums</a></li>
                    <li{% if nav_section=="gallery" %} class="selected-nav"{% endif %}><a href="/gallery/post/">Gallery</a></li>
                    <li{% if nav_section=="fics" %} class="selected-nav"{% endif %}><a href="/fics/">Fics</a></li>
                    <li{% if nav_section=="user" %} class="selected-nav"{% endif %}><a href="/user/list/">Users</a></li>
                    <li{% if nav_section=="about" %} class="selected-nav"{% endif %}><a href="/about/">About</a></li>
                    <li><a href="/setup/sql_setup.php">DEBUG Setup</a></li>
                    <li><a href="/login/?debug=true">DEBUG Login</a></li>
                </ul>
            {% endblock %}
            <div class="Clear">&nbsp;</div>
        </div>
        <div id="mainbody">
            <div id="header">{# TODO: Replace with actual site banner #}
                <hr />
                {% block welcome %}
                    <h1>Welcome, {% if user %}{{ user.DisplayName }}{% else %}Guest{% endif %}!</h1>
                {% endblock %}
                <hr />
                {% block section_navigation %}
                {% endblock %}
            </div>
            <div class="Clear">&nbsp;</div>
            <div id="content">
                {% if error_msg %}
                    <div class="error-box">
                        <p class="error-msg">
                            {{ error_msg }}
                        </div>
                    </div>
                {% else %}
                    {% block content %}
                        {% if error_msg %}
                            {{ error_msg }}
                        {% elseif content %}
                            {{ content }}
                        {% else %}
                            No content here.
                        {% endif %}
                    {% endblock %}
                {% endif %}
            </div>
            <div class="Clear" style="display: block">&nbsp;</div>
        </div>
        <div id="footer">
            {% block footer %}
                <span><small>Copyright AGNPH 2015</small></span>
            {% endblock %}
        </div>
    </body>
</html>
{% endspaceless %}