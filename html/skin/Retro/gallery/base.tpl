{% extends 'base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/gallery/style.css')|raw }}
    {{ inline_css_asset('/gallery/retro-style.css')|raw }}
    {{ inline_css_asset('/no-left-panel-mobile-style.css')|raw }}
    {{ inline_css_asset('/no-right-panel-style.css')|raw }}
{% endblock %}

{% block section_navigation %}
    <li><a href="/gallery/post/">Index</a></li>
    <li><a href="/gallery/tags/">Tags</a></li>
    <li><a href="/gallery/pools/">Pools</a></li>
    <li><a href="/gallery/post/?search=order%3Apopular">Popular</a></li>
    {% block extra_section_nav_items %}
        {# For slideshow links, etc #}
    {% endblock %}
{% endblock %}

{% block page_title_bar %}
    <div id="gallery-search">
        <form action="/gallery/post/" accept-charset="UTF-8">
            <div class="search">
                <input class="search" name="search" value="{{ search }}" type="text" required placeholder="Search" />
                <input type="submit" class="search-button" value="" />
            </div>
            <a id="search-help-link" href="/gallery/help/" title="Search Help">?</a>
        </form>
    </div>
    <strong>AGNPH Gallery</strong>
{% endblock %}

{% block extra_account_menu_options_logged_in %}
    <li><a href="/gallery/upload/">Upload</a></li>
    <li><a href="/gallery/post/?search=fav%3Ame">My Favorites</a></li>
{% endblock %}

{% block content %}
{% endblock %}
