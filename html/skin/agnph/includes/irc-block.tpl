{% block irc_block %}
    {% if user %}
    <div class="block" id="irc-block">
        <div class="header">IRC/Discord</div>
        <div class="content">
            <div class="irc-active">
                Active Users: <span id="active-user">Unknown</span>
            </div>
            <div class="irc-log-container">
                <div class="irc-log">
                    Loading...
                </div>
            </div>
            <div>
                <a href="https://discord.gg/uHbHMv7">Chat Now</a>
            </div>
        </div>
    </div>
    {% endif %}
{% endblock %}
