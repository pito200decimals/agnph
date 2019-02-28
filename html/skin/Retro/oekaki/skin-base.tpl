{% extends "oekaki/base.tpl" %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/oekaki/retro-style.css')|raw }}
    {{ inline_css_asset('/no-left-panel-mobile-style.css')|raw }}
    {{ inline_css_asset('/no-right-panel-style.css')|raw }}
{% endblock %}

{% block section_navigation %}
    <ul class="section-nav">
        <li><a href="/oekaki/">Index</a></li>
    </ul>
    <div class="Clear">&nbsp;</div>{# for search box float alignment #}
{% endblock %}

{% block page_title_bar %}
    <div id="oekaki-search">
        <form action="/oekaki/" accept-charset="UTF-8">
            <div class="search">
                <input class="search" name="search" value="{{ search }}" type="text" required placeholder="Search" />
                <input type="submit" class="search-button" value="" />
            </div>
        </form>
    </div>
    <strong>AGNPH Oekaki</strong>
{% endblock %}

{% block extra_account_menu_options_logged_in %}
    <li><a href="/oekaki/draw/">Draw</a></li>
{% endblock %}