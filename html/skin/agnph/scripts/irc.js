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