{% extends "about/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
{% endblock %}

{% block content %}
    <h3>AGNPH Help Pages (Minecraft)</h3>
    <div class="block">
        <div class="header">AGNPH Minecraft Server Info</div>
        <div class="content">
            Server Address: <strong>mc.agn.ph</strong><br />
            Game Mode: <strong>Survival</strong><br />
            Minecraft Version: <strong>1.8</strong><br />
            <p>
                <span class="warning">You must be at least 18 years or older to access the AGNPH Minecraft server.</span>
            </p>
            <p>
                Download the official Minecraft launcher: <strong><a href="https://s3.amazonaws.com/Minecraft.Download/launcher/Minecraft.exe">Windows</a> / <a href="https://s3.amazonaws.com/Minecraft.Download/launcher/Minecraft.dmg">Mac OS X</a> / <a href="https://s3.amazonaws.com/Minecraft.Download/launcher/Minecraft.jar">Linux</a></strong>
            </p>
        </div>
    </div>
    <div class="block">
        <div class="header">Plugins and Mods</div>
        <div class="content">
            <p>
                AGNPH's minecraf server is running several plugins, but the most notable is GriefPrevention, which allows players to 'claim' land so others can't build in it without permission.
                Additionally, the minecraft server is hooked up to the IRC chat, in <strong>#agnph-mc</strong>. People in both chats can speak and will be seen on the other.
            </p>
            <p>
                We allows most client-side mods, under the condition that they do not impact gameplay. Allowed examples include texture/shader packs and client-side map mods.
            </p>
            <p>
                The server <strong>does not</strong> allow mods like flying or xray, and you will be banned if you use these.
            </p>
        </div>
    </div>
    <div class="block">
        <div class="header">Oh no! The server is offline, or something is broken!</div>
        <div class="content">
            Most likely we are already aware of the issue and are working to resolve the problem. It is also possible that we are performing scheduled maintenance.
            If you would like to alert us about a potential problem, please contact us on IRC. When we see your message in the channel (#agnph-mc), we'll respond.
        </div>
    </div>
    <div class="block">
        <div class="header">How to claim land using GriefPrevention</div>
        <div class="content">
            <p>
                All players can create their first claim by <strong>placing down their first chest</strong>. This will claim a small area around the chest that only you can build in.
            </p>
            <p>
                To expand a claim or to create a new one, you must craft a <strong>golden shovel</strong>, then right-click with it to modify/create a claim.
            </p>
            <p>
                If you have any questions about what commands are available to players, ask around on the Minecraft server.
            </p>
        </div>
    </div>
{% endblock %}
