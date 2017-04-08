{# Define blocks to be used elsewhere #}
{% if false %}
    {# Notification banners #}
    {% block banner %}
        {% for notification in banner_notifications %}
            <div class="banner-notification{% for class in notification.classes %} {{ class }}{% endfor %}">
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
        {# Disable all HTML caching #}
        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="expires" content="0" />
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <meta http-equiv="pragma" content="no-cache" />
        {# end disable-caching #}
        {% include 'meta.tpl' %}
        <link rel="icon" type="image/png" href="/images/favicon.png" />
        <title>{% if _title %}{{ _title }}{% else %}{% block title %}AGNPH{% endblock %}{% endif %}</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('/shared.css') }}" />
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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="{{ asset('/base.js') }}"></script>
        <script src="{{ asset('/scripts/zoom.js') }}"></script>
        {% block scripts %}
            {# Custom page scripts go here #}
        {% endblock %}
    </head>
    <body>
        <div id="site-top-full-bar">
            <div id="site-top-full-bar-container">{# For restricting nav to site main width #}
                <ul id="account-links" class="nav-menu">
                    {% if user %}
<noscript>
                        <li>
                            <form action="/logout/" method="POST" accept-encoding="UTF-8">
                                <input type="submit" name="submit" value="Log out" />
                            </form>
                        </li>
</noscript>
                        <li>
                            <a class="desktop-only" href="/user/{{ user.UserId }}/mail/">
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
                        <li id="account-menu-button">
                            <a href="/user/{{ user.UserId }}/"><img src="{{ user.avatarURL }}" /><span class="desktop-only">{{ user.DisplayName }}</span></a>
                            <ul id="account-menu" class="nav-menu-tray">
                                <li><a href="/user/{{ user.UserId }}/">Profile</a></li>
                                <li>
                                    <div class="mobile-only"><a href="/user/{{ user.UserId }}/mail/">Messages{% if unread_message_count > 0 %} <span class="unread-messages">({{ unread_message_count }})</span>{% endif %}</a></div>
                                </li>
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
                        <li class="desktop-only"><a href="/login/">Login</a></li>
                        <li class="desktop-only"><a href="/register/">Register</a></li>
                        <li class="mobile-only mobile-navbar-right-links">
                            <a href="/register/">Register</a>
                            <a href="/login/">Login</a>
                        </li>
                    {% endif %}
                </ul>
                <img id="main-menu-icon" class="mobile-only" src="/images/menu-icon.png" />
                <div id="mobile-logo-container" class="mobile-only">
                    <img id="mobile-logo" src="/images/logo_300.png" />
                </div>
                <ul id="main-nav-menu" class="nav-menu nav-menu-tray">
                    <li{% if nav_section=="home" %} class="selected-nav"{% endif %}><a href="/">Home</a></li>
                    <li{% if nav_section=="forums" %} class="selected-nav"{% endif %}><a href="/forums/board/">Forums</a></li>
                    <li{% if nav_section=="gallery" %} class="selected-nav"{% endif %}><a href="/gallery/post/">Gallery</a></li>
                    <li{% if nav_section=="fics" %} class="selected-nav"{% endif %}><a href="/fics/">Fics</a></li>
                    <li{% if nav_section=="oekaki" %} class="selected-nav"{% endif %}><a href="/oekaki/">Oekaki</a></li>
                    <li{% if nav_section=="user" %} class="selected-nav"{% endif %}><a href="/user/list/">Users</a></li>
                    <li><a href="/about/irc/">IRC</a></li>
                    <li{% if nav_section=="about" %} class="selected-nav"{% endif %}><a href="/about/">About</a></li>
                    {% if user.showAdminTab %}<li{% if nav_section=="admin" %} class="selected-nav"{% endif %}><a href="/admin/">Admin</a></li>{% endif %}
                </ul>
            </div>
            <div class="Clear">&nbsp;</div>
        </div>
        <div id="main-body">
            <div id="desktop-logo-container" class="desktop-only">
                <img id="desktop-logo" src="/images/logo_300.png" />
            </div>
            <div class="Clear">&nbsp;</div>
            <div id="main-content-header">
                {% block section_navigation %}
                {% endblock %}
            </div>
            <div class="Clear">&nbsp;</div>
            <div id="main-content" class="font-scalable">
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
            <div class="Clear">&nbsp;</div>
            <div id="main-content-footer">
                {% block theme_select %}
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
                {% endblock %}
                <span class="font-size-switcher-container site-font-size-switcher" hidden>
                    Font Size:
                    <select>
                        <option>80%</option>
                        <option>90%</option>
                        <option>100%</option>
                        <option>120%</option>
                        <option>150%</option>
                    </select>
                </span>
                <div class="Clear">&nbsp;</div>
            </div>
        </div>
        <div id="footer">
            <div><span>© 1996-2017 AGNPH</span> | <span>v{{ version }}</span> | <span><a href="/about/rules/">Rules</a></span> | <span><a href="/about/staff/">Contact Us</a></span></div>
            <div></div>
            <div><span><small>All fanworks within are based on Pokémon. Pokémon © Nintendo/Creatures, Inc./GAME FREAK/The Pokémon Company. All work contained within this website are user-submitted, fan-made contributions. No copyright infringement is intended.</small></span></div>
        </div>
    </body>
</html>