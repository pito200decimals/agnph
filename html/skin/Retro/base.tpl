{% set mobile_condensed_header=false %}
{% set site_nav_in_tray=true %}

{# Define blocks to be used elsewhere #}
{% if false %}
    {# Notification banners #}
    {% block banner %}
        {% for notification in banner_notifications %}
            <div class="banner-notification{% for class in notification.classes %}{{ " " }}{{ class }}{% endfor %}">
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
    {% block desktop_site_navigation %}
        <ul class="site-navigation{% if site_nav_in_tray%}{{ " " }}desktop-only{% endif %}">
            <li><a href="/">agn.ph</a></li>&nbsp;&nbsp;
            <li><a href="/fics/">/fics</a></li>
            - <li><a href="/gallery/post/">/gallery</a></li>
            - <li><a href="/forums/">/forums</a></li>
            - <li><a href="/oekaki/">/oekaki</a></li>
            - <li><a href="/user/list/">/users</a></li>
            - <li><a href="/about/">/about</a></li>
        </ul>
    {% endblock %}
    {% block mobile_site_navigation %}
        {% if site_nav_in_tray %}
            <li class="mobile-only"><a href="/">AGNPH</a></li>
            <li class="mobile-only"><a href="/fics/">Fics</a></li>
            <li class="mobile-only"><a href="/gallery/post/">Gallery</a></li>
            <li class="mobile-only"><a href="/forums/">Forums</a></li>
            <li class="mobile-only"><a href="/oekaki/">Oekaki</a></li>
            <li class="mobile-only"><a href="/user/list/">Users</a></li>
            <li class="mobile-only"><a href="/about/">About</a></li>
            <li class="mobile-only divider"></li>
        {% endif %}
    {% endblock %}
    {% block my_account_link %}
    {% endblock %}
    {% block account_box_desktop %}
        <div class="block desktop-only">
            <div class="header">Account</div>
            <div class="content">
                <ul class="section-nav">
                    {% block account_box_desktop_items %}
                        {% if user %}
                            {% if user.showAdminTab %}
                                <li><a href="/admin/">Admin Panel</a></li>
                            {% endif %}
                            <li><a href="/user/{{ user.UserId }}/">My Account{% if not hide_account_unread_count and unread_message_count + unread_notification_count > 0 %} <span class="unread-messages">({{ unread_message_count + unread_notification_count }})</span>{% endif %}</a></li>
                            {{ block('extra_account_menu_options_logged_in') }}
                        {% else %}
                            <li><a href="/register/">Register</a></li>
                            <li><a href="/login/">Log In</a></li>
                        {% endif %}
                    {% endblock %}
                </ul>
            </div>
        </div>
    {% endblock %}
    {% block account_box_mobile %}
        {% if user %}
            {% if user.showAdminTab %}
                <li class="mobile-only"><a href="/admin/">Admin Panel</a></li>
            {% endif %}
            <li class="mobile-only"><a href="/user/{{ user.UserId }}/">My Account{% if not hide_account_unread_count and unread_message_count + unread_notification_count > 0 %} <span class="unread-messages">({{ unread_message_count + unread_notification_count }})</span>{% endif %}</a></li>
            <li class="mobile-only">
                <ul class="section-nav">
                    {{ block('extra_account_menu_options_logged_in') }}
                </ul>
            </li>
        {% else %}
            <li class="mobile-only"><a href="/register/">Register</a></li>
            <li class="mobile-only"><a href="/login/">Log In</a></li>
        {% endif %}
    {% endblock %}
    {% block extra_account_menu_options_logged_in %}
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
        <link rel="icon" type="image/png" href="{{ asset('/favicon.ico') }}" />
        <title>{% if _title %}{{ _title }}{% else %}{% block title %}AGNPH{% endblock %}{% endif %}</title>
        {{ inline_css_asset('/shared.css')|raw }}
        {{ inline_css_asset('/style.css')|raw }}
        {{ inline_css_asset('/index-style.css')|raw }}
        {% block styles %}
            {# Custom page styles go here #}
        {% endblock %}
        {{ inline_css_asset('/color.css')|raw }}
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
        <script src="{{ asset('/scripts/jquery.min.js') }}"></script>
        <script src="{{ asset('/base.js') }}"></script>
        <script src="{{ asset('/scripts/zoom.js') }}"></script>
        {% block scripts %}
            {# Custom page scripts go here #}
        {% endblock %}
    </head>
    <body>
        <div id="site-body-width-container">
            <div id="site-header"{% if mobile_condensed_header %}{{ " " }}class="desktop-only"{% endif %}>
                <div id="site-logo-container">
                    {% if user %}
                        <img src="/skin/Retro/logo.png" />
                    {% else %}
                        <img src="/skin/Retro/logo-clean.png" />
                    {% endif %}
                </div>
                <div id="site-navigation-container">
                    {{ block('desktop_site_navigation') }}
                </div>
            </div>
            <div id="main-body">
                <div id="page-title-bar">
                    <div id="page-title">
                        {% block page_title_bar %}
                            <strong>AGNPH</strong>
                        {% endblock %}
                    </div>
                </div>
                <div id="main-content" class="font-scalable">
                    <div id="left-content-panel">
                        {% block section_navigation_tray %}
                            <div id="section-nav-tray-container">
                                <input id="section-nav-checkbox" type="checkbox" />
                                <div id="section-nav-tray-overlay" class="animated-tray"></div>
                                <label for="section-nav-checkbox" id="section-nav-label" class="animated-tray">
                                    <!--<img src="/skin/Retro/menu-icon.png" class="animated-tray" />-->
                                    <img src="/images/menu-icon.png" class="animated-tray" />
                                </label>
                                <div id="section-nav-tray" class="animated-tray">
                                    <ul class="section-nav">
                                        {{ block('mobile_site_navigation') }}
                                        {% block section_navigation %}
                                        {% endblock %}
                                        <li class="divider mobile-only"></li>
                                        {{ block('account_box_mobile') }}
                                    </ul>
                                </div>
                            </div>
                        {% endblock %}
                        <div id="left-content">
                            {% block left_panel %}
                                {{ block('account_box_desktop') }}
                            {% endblock %}
                        </div>
                    </div>
                    <div id="center-right-content-panel">
                        <div id="right-content-panel">
                            <div id="right-content">
                                {% block right_panel %}
                                {% endblock %}
                            </div>
                        </div>
                        <div id="center-content-panel">
                            <div id="main-content">
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
                    </div>
                    <div class="Clear">&nbsp;</div>
                    <div id="main-content-footer">
                        <div id="main-footer-container">
                            {% block theme_select %}
                                <span id="theme-switcher">
                                    Theme:
                                    <form id="theme-switcher-form" action="/change-skin/" method="POST" accept-encoding="UTF-8">
                                        <select name="skin" onchange="document.getElementById('theme-switcher-form').submit();">
                                            {% for s in availableSkins %}
                                                <option{% if s == skin %}{{ " " }}selected{% endif %}>{{ s }}</option>
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
                        </div>
                        <div class="Clear">&nbsp;</div>
                    </div>
                    <div class="Clear">&nbsp;</div>
                </div>
            </div>
            <div id="footer">
                {% for entry in debug_timing %}
                    <div>{{ entry.description }}: {{ entry.time_ms }} ms</div>
                {% endfor %}
                <div><span>© 1996-{{ copyright_year }} AGNPH</span> | <span>v{{ version }}</span> | <span><a href="/about/rules/">Rules</a></span> | <span><a href="/about/privacy/">Privacy Policy</a></span> | <span><a href="/about/staff/">Contact Us</a></span></div>
                <div><span><small>All fanworks within are based on Pokémon. Pokémon © Nintendo/Creatures, Inc./GAME FREAK/The Pokémon Company. All work contained within this website are user-submitted, fan-made contributions. No copyright infringement is intended.</small></span></div>
            </div>
        </div>
    </body>
</html>