{% extends "about/base.tpl" %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
    {% endif %}
{% endblock %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
{% endblock %}

{% block content %}
    <h3>AGNPH Info</h3>
    <div class="block">
        <div class="header">About</div>
        <div class="content">
            <strong>AGNPH,</strong> (or <em>alt.games.nintendo.pokémon.hentai</em>), is an internet archive started around 1999 to house all manner of explicit pokémon content. <span class="warning">You must be 18 years or older to visit this site.</span>
        </div>
    </div>
    <div class="block">
        <div class="header">History</div>
        <div class="content">
            AGNPH has been around in some form or another since 1996. Originally just a newsgroup, it quickly expanded into a standalone site to house the group's growing archive in 1999. Since then, it's been through at least 4 versions of the site.
        </div>
    </div>
{% endblock %}
