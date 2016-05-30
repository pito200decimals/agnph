{% extends "base.tpl" %}

{% block scripts %}
    {{ parent() }}
{% endblock %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/oekaki/style.css') }}" />
{% endblock %}

{% block section_navigation %}
    <ul class="section-nav">
        <li><a href="/oekaki/">Index</a></li>
        {#<li><a href="/oekaki/">Browse</a></li>#}
        {% if user %}
            <li><a href="/oekaki/draw/">Draw</a></li>
            <li><a href="/user/{{ user.UserId }}/oekaki/">My Oekaki</a></li>
        {% endif %}
        <li id="oekaki-search">
            <form action="/oekaki/" accept-charset="UTF-8">
                <div class="search">
                    <input class="search" name="search" value="{{ searchTerms }}" type="text" required placeholder="Search" onfocus="javascript:$(this).attr('placeholder', '');" onblur="javascript:$(this).attr('placeholder', 'Search');" />
                    <input type="submit" class="search-button" value="" />
                </div>
                {#<a id="search-help-link" href="/fics/help/" title="Search Help">?</a>#}
            </form>
        </li>
    </ul>
    <div class="Clear">&nbsp;</div>{# for search box float alignment #}
{% endblock %}

{% block content %}
{% endblock %}
