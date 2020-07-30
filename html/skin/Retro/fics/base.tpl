{% extends 'base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/fics/style.css')|raw }}
    {{ inline_css_asset('/no-left-panel-mobile-style.css')|raw }}
    {% if not enable_right_panel %}
        {{ inline_css_asset('/no-right-panel-style.css')|raw }}
    {% endif %}
{% endblock %}

{% block section_navigation %}
    <li><a href="/fics/">Index</a></li>
    <li><a href="/fics/browse/">Stories</a></li>
    <li><a href="/fics/authors/">Authors</a></li>
    <li><a href="/fics/tags/">Tags</a></li>
    <li><a href="/fics/rss.xml">RSS</a></li>
{% endblock %}

{% block page_title_bar %}
    <div id="fics-search">
        <form action="/fics/browse/" accept-charset="UTF-8">
            <div class="search">
                <input class="search" name="search" value="{{ search }}" type="text" required placeholder="Search" />
                <input type="submit" class="search-button" value="" />
            </div>
            <a id="search-help-link" href="/fics/help/" title="Search Help">?</a>
        </form>
    </div>
    <strong>AGNPH Stories</strong>
{% endblock %}

{% block extra_account_menu_options_logged_in %}
    <li><a href="/fics/create/">Upload</a></li>
    <li><a href="/user/{{ user.UserId }}/fics/">My Stories</a></li>
    <li><a href="/fics/browse/?search=fav%3Ame">My Favorites</a></li>
{% endblock %}

{% block content %}
{% endblock %}
