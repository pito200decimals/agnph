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
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="icon" type="image/png" href="/images/favicon.png" />
        <title>{% if _title %}{{ _title }}{% else %}{% block title %}AGNPH{% endblock %}{% endif %}</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="{{ asset('/base.js') }}"></script>
        {% block scripts %}
            {# Custom page scripts go here #}
        {% endblock %}
        <link rel="stylesheet" type="text/css" href="{{ asset('/style.css') }}" />
        {% block styles %}
            {# Custom page styles go here #}
        {% endblock %}
        <link rel="stylesheet" type="text/css" href="{{ asset('/color.css') }}" />
        {% if debug %}
            {# Dot all borders to help style debugging #}
            {# TODO: Remove #}
            <style>
                div,span,li,td {
                    border-style: dotted;
                    border-width: 1px;
                }
            </style>
        {% endif %}
    </head>
    <body>
        <div class="site-navbar">
            <div id="site-navbar-container">
                {# float right goes before normal nav, so it positions correctly #}
                <ul class="navigation_right">
                    {% if user %}
<noscript>
                        <li class="navigation_left">
                            <form action="/logout/" method="POST" accept-encoding="UTF-8">
                                <input type="submit" name="submit" value="Log out" />
                            </form>
                        </li>
</noscript>
                        <li class="navigation_left">
                            <a id="mail_icon" href="/user/{{ user.UserId }}/mail/">
                                {% if unread_message_count > 0 %}
                                    {% if unread_message_count <= 9 %}
                                        <img src="/images/message-unread-{{ unread_message_count }}.png" />
                                    {% else %}
                                        <img src="/images/message-unread-9+.png" />
                                    {% endif %}
                                {% else %}
                                    <img src="/images/message.png" />
                                {% endif %}
                            </a>
                        </li>
                        <li id="account_link">
                            <a href="/user/{{ user.UserId }}/"><img src="{{ user.avatarURL }}" />{{ user.DisplayName }}</a>
                            <ul id="account_dropdown" class="" hidden>
                                <li><a href="/user/{{ user.UserId }}/">Profile</a></li>
                                <li><a href="/user/{{ user.UserId }}/preferences/">Settings</a></li>
                                <li>
                                    <form action="/logout/" method="POST" accept-encoding="UTF-8">
                                        <input type="submit" name="submit" value="Log out" />
                                    </form>
                                </li>
                                {#<li><a href="/logout/">Log out</a></li>#}
                            </ul>
                        </li>
                    {% else %}
                        <li><a href="/login/">Login</a></li>
                        <li><a href="/register/">Register</a></li>
                    {% endif %}
                </ul>
                <img id="main_menu_icon" src="/images/menu-icon.png" />
                {# TODO: Adjust logo #}
                {# <a id="logo" href="/"><img src="/images/logo.png" /></a> #}
                <ul class="navigation_left">
                    {# Uncomment if logo as home is desired #}
                    {#
                    <li class="desktop-only"><a id="top-logo-link" href="/"><img id="top-logo" src="/images/logo.png" /></a></li>
                    <li class="mobile-only{% if nav_section=="home" %} selected-nav{% endif %}"><a href="/">Home</a></li>
                    #}
                    <li{% if nav_section=="home" %} class="selected-nav"{% endif %}><a href="/">Home</a></li>
                    <li{% if nav_section=="forums" %} class="selected-nav"{% endif %}><a href="/forums/board/">Forums</a></li>
                    <li{% if nav_section=="gallery" %} class="selected-nav"{% endif %}><a href="/gallery/post/">Gallery</a></li>
                    <li{% if nav_section=="fics" %} class="selected-nav"{% endif %}><a href="/fics/">Fics</a></li>
                    {#<li{% if nav_section=="oekaki" %} class="selected-nav"{% endif %}><a href="/oekaki/">Oekaki</a></li>#}
                    <li{% if nav_section=="user" %} class="selected-nav"{% endif %}><a href="/user/list/">Users</a></li>
                    <li{% if nav_section=="about" %} class="selected-nav"{% endif %}><a href="/about/">About</a></li>
                    {% if user.showAdminTab %}<li{% if nav_section=="admin" %} class="selected-nav"{% endif %}><a href="/admin/">Admin</a></li>{% endif %}<!--
                    {# TODO: Remove after debugging complete #}<li><a href="/setup/">DEBUG Setup</a></li>
                    {# TODO: Remove after debugging complete #}{% if not user %}<li><a href="/login/?debug=true">DEBUG Login</a></li>{% endif %} -->
                </ul>
            </div>
            <div class="Clear">&nbsp;</div>
        </div>
        <div id="mainbody">
            <div id="maincontent">
                <div id="logo">
                    <img src="/images/banner.png" style="max-width: 100%;" />
                </div>
                <div id="header">
                    {% block section_navigation %}
                    {% endblock %}
                </div>
                <div id="content">
                    {% if error_msg %}
                        <div class="base-error-msg">
                            <p>
                                {{ error_msg }}
                            </p>
                        </div>
                    {% else %}
                        {% block content %}
                            {# Default error message if template is broken, should never show #}
                            <p style="text-align: center;">
                                Page not found
                            </p>
                        {% endblock %}
                    {% endif %}
                </div>
            </div>
            <div id="mainfooter">
                <span id="theme-switcher">
                    Theme:
                    <form id="theme-switcher-form" action="/change-skin/" method="POST" accept-encoding="UTF-8">
                        <select name="skin" onchange="document.getElementById('theme-switcher-form').submit();">
                            {% for s in availableSkins %}
                                <option{% if s == skin %} selected{% endif %}>{{ s }}</option>
                            {% endfor %}
                        </select>
                    </form>
                </span>
                <div class="Clear">&nbsp;</div>
            </div>
        </div>
        <div id="footer">
            <div><span>© 1996-2015 AGNPH</span> | <span>v{{ version }}</span> | <span><a href="/about/rules/">Rules</a></span> | <span><a href="/about/staff/">Contact Us</a></span></div>
            <div></div>
            <div><span><small>All fanworks within are based on Pokémon. Pokémon © Nintendo/Creatures, Inc./GAME FREAK/The Pokémon Company. All work contained within this website are user-submitted, fan-made contributions. No copyright infringement is intended.</small></span></div>
        </div>
    </body>
</html>
{% endspaceless %}