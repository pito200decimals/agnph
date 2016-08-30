{% block irc_block %}
    <div class="block" id="irc-block">
        <div class="header">IRC</div>
        <div class="content">
            <div class="irc-active">
                Active Users: <span id="active-user">Unknown</span>
            </div>
            <div class="irc-log-container">
                <div class="irc-log">
                    Loading...
                </div>
            </div>
                <a href="http://irc.agn.ph/?initial_chans=#agnph{% if user %}&initial_nick={{user.DisplayName|replace({" ":"_"})}}{% endif %}">Chat Now</a>
            </div>
        </div>
    </div>
{% endblock %}
