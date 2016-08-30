{% extends "about/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
{% endblock %}

{% block content %}
    <h3>AGNPH Help Pages (IRC)</h3>
    <div class="block">
        <div class="header">I want to get on IRC right now!</div>
        <div class="content">
            Use our Browser IRC Client! Its fun and its easy.<br />
            Just <a href="http://irc.agn.ph/?initial_chans=#agnph{% if user %}&initial_nick={{user.DisplayName|replace({" ":"_"})}}{% endif %}"><strong>Click Here</strong></a>.
        </div>
    </div>
    <div class="block">
        <div class="header">What is IRC?</div>
        <div class="content">
            IRC stands for Internet Relay Chat. It's an older and very simple method of communication on the internet that has been around since the late 80s.
            It was created in 1988 and is a very well established protocol, with clients and services for IRC available for pretty much every type of device and operating system.
            For more information, read the <a href="https://en.wikipedia.org/wiki/Internet_Relay_Chat">Wikipedia page</a>.
        </div>
    </div>
    <div class="block">
        <div class="header">How can I access the IRC?</div>
        <div class="content">
            The most reliable and most encouraged method of using the IRC is to use one of the many IRC client apps that exist for practically every operating system and device.
            Popular app-based clients include:
            <ul>
                <li>YChat (Windows)</li>
                <li>mIRC (Windows)</li>
                <li>HexChat (Windows)</li>
                <li>Pidgin (Windows, Linux)</li>
                <li>XChat (Linux)</li>
                <li>Colloquy (Mac OS X)</li>
                <li>Limechat (Mac OS X)</li>
                <li>AndChat (Android)</li>
                <li>IRChon (iPhone and iPad (iOS))</li>
            </ul>
        </div>
    </div>
    <div class="block">
        <div class="header">What details do I enter into my IRC Client?</div>
        <div class="content">
            When configuring the client to use the AGNPH server, the following information should be used:
            <ul>
                <li>Server: <strong>irc.agn.ph</strong></li>
                <li>Port: <strong>6667</strong></li>
                <li>Nick: The nickname you want to use.</li>
                <li>Channel: <strong>#agnph</strong></li>
            </ul>
            If irc.agn.ph isn't working, you can try two other servers on the same irc network, <strong>irc.kitsunet.net</strong> and <strong>irc.digibase.ca</strong>.
            If your IRC client continues to have problems connecting, be sure to contact a staff member for assistance.
        </div>
    </div>
    <div class="block">
        <div class="header">Common Chat Commands in IRC</div>
        <div class="content">
            Here is a list of the most common commands you can use in IRC. Note that all channels have a name starting with '#', so be sure to include that.
            <ul>
                <li>
                    <p>
                        <strong>/join #channel</strong><br />
                        Joins a chat channel.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>/part #channel</strong><br />
                        Leaves one of the chat channels you are in.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>/query (nick)</strong><br />
                        Opens a new private chat with a single person. Replace "(nick)" with the nickname of the person you want to chat with.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>/msg nickserv register (password) (email)</strong><br />
                        Register your nickname to keep other people from using it. This reserves the nickname you currently have for you to use permanently.
                        Replace "(password)" with a password to log in with and "(email)" with your email address.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>/msg nickserv identify (password)</strong><br />
                        Logs in with your registered nickname. Replace "(password)" with the password you registered with.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>/msg nickserv help</strong><br />
                        Lists all the commands you can use with NickServ, including more detailed descriptions of the register and identify commands above.
                    </p>
                </li>
            </ul>
        </div>
    </div>
    <div class="block">
        <div class="header">What services are available on IRC?</div>
        <div class="content">
            We have the standard <strong>NickServ</strong> and <strong>ChanServ</strong> services available on our IRC network. Additionally, there is a bot user named <strong>Dexter</strong> that resides in
            <strong>#agnph</strong> and provides some commonly-used functionality. For more info, ask him "!help", or just ask anyone in the channel.
        </div>
    </div>
{% endblock %}
