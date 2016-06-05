{% extends "base.tpl" %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
    {% endif %}
    {% if user %}
        <script>
            $(document).ready(function() {
                function RefreshChat() {
                    if (!$("#irc-block").is(":visible")) {
                        setTimeout(RefreshChat, 10 * 1000);
                        return;
                    }
                    function ShowData(data) {
                        $("#active-user").text(data.active);
                        var container = $(".irc-log-container");
                        var log = container.find(".irc-log");
                        var bottomScrollPos = container.prop('scrollHeight') - container.innerHeight();
                        var shouldScroll = true;
                        if (container.scrollTop() < bottomScrollPos) shouldScroll = false;
                        log.html("");
                        for (var i = 0; i < data.log.length; i++) {
                            var item = data.log[i];
                            var logLine = $("<div></div>");
                            logLine.append($("<span class='irc-timestamp'>[" + item.time + "]</span>").text("[" + item.time + "]"));
                            if (item.type == "msg") {
                                logLine.append($("<span class='irc-nick'></span>").text("<" + item.nick + ">"));
                                logLine.append($("<span class='irc-msg'></span>").text(item.text));
                            } else if (item.type == "action") {
                                logLine.append($("<span class='irc-action'></span>").text("* " + item.nick + " " + item.text));
                            } else if (item.type == "nick") {
                                logLine.append($("<span class='irc-changenick'></span>").text(item.nick + " is now known as " + item["new-nick"]));
                            } else if (item.type == "join") {
                                logLine.append($("<span class='irc-join'></span>").text(item.nick + " has joined #agnph"));
                            } else if (item.type == "part") {
                                logLine.append($("<span class='irc-part'></span>").text(item.nick + " has left #agnph"));
                            } else if (item.type == "quit") {
                                logLine.append($("<span class='irc-quit'></span>").text(item.nick + " has quit"));
                            } else {
                                continue;
                            }
                            log.append(logLine);
                        }
                        if (shouldScroll) {
                            container.scrollTop(container.prop('scrollHeight'));
                        }
                    }
                    $.ajax("/irc/status/", {
                        method: "GET",
                        success: function(data) {
                            ShowData(data);
                            setTimeout(RefreshChat, 10 * 1000);
                        },
                        error: function(e) {
                            console.log(e);
                            setTimeout(RefreshChat, 60 * 1000);
                        }
                    });
                }
                RefreshChat();
            });
        </script>
    {% endif %}
{% endblock %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/index-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/comments-style.css') }}" />
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div class="index-table">
        <div class="right-column">
            <div class="column-contents">
                {% if user %}
                    <div class="block desktop-only" id="irc-block">
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
                            <div class="irc-links">
                                <a href="http://irc.agn.ph/">Chat Now</a>
                            </div>
                        </div>
                    </div>
                {% endif %}
            </div>
        </div>


        <div class="center-column">
            <div class="column-contents">
                {% if welcome_message %}
                    <div class="block">
                        <div class="header">Welcome</div>
                        <div class="content">{% autoescape false %}{{ welcome_message }}{% endautoescape %}</div>
                    </div>
                {% endif %}
                {% if news|length > 0 %}
                    <h3>Recent News</h3>
                    {% for post in news %}
                        <div class="block">
                            <div class="header">
                                {{ post.section }} - <a href="/forums/thread/{{ post.PostId }}/">{{ post.Title }}</a>
                                <div class="tagline">
                                    Posted {{ post.date }} by <a href="/user/{{ post.user.UserId }}/">{{ post.user.DisplayName }}</a>
                                </div>
                                <div class="Clear">&nbsp;</div>
                            </div>
                            <div class="content">
                                {% autoescape false %}{{ post.Text }}{% endautoescape %}
                            </div>
                        </div>
                    {% endfor %}
                {% endif %}
            </div>
        </div>


        <div class="left-column">
            <div class="column-contents">
                {% if events %}
                    <div class="block">
                        <div class="header">Events</div>
                        <div class="content">{% autoescape false %}{{ events }}{% endautoescape %}</div>
                    </div>
                {% endif %}
            </div>
        </div>
    </div>
{% endblock %}
