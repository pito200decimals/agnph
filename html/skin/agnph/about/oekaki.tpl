{% extends "about/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
    <style>
        .block .content a {
            font-weight: bold;
        }
    </style>
{% endblock %}

{% block content %}
    <h3>AGNPH Help Pages (Oekaki)</h3>
    <div class="block">
        <div class="header">What is the Oekaki?</div>
        <div class="content">
            <p>
                The Oekaki is an in-browser drawing app that allows artists to draw/sketch a piece, then post it directly to the site.
                Most modern browsers should be supported correctly, but let a staff member know if you find any bugs or have ideas on
                how to make the software better.
            </p>
            <p>
                <a href="/oekaki/draw/">Click here</a> to get started drawing!
            </p>
        </div>
    </div>
    <div class="block">
        <div class="header">What about my old Oekaki account?</div>
        <div class="content">
            <p>
                If you used to have an oekaki account, don't fear! It's still on the site, and you can log in with it as normal
                (or import/merge it into your existing AGNPH account). All your previous image posts should still be visible.
            </p>
        </div>
    </div>
    <div class="block">
        <div class="header">Pen Pressure Support</div>
        <div class="content">
            <p>
                The oekaki supports pen pressure on some tablet devices. Refer to the following list depending on your browser.
            </p>
            <p>
                <strong>Mozilla Firefox</strong><br />
                Wacom tablet drivers should include firefox plugin support when you install them. You will be prompted to allow the plugin when first visiting the oekaki.<br />
                <ul>
                    <li>
                        <a href="http://www.wacomeng.com/web/fbWTPInstall.zip">Wacom Plugin</a>
                    </li>
                </ul>
                <small><strong>Note:</strong> Native plugin support may be removed from Firefox later in 2016.</small>
            </p>
            <p>
                <strong>Google Chrome</strong><br />
                Pen pressure in Chrome is supported via chrome extension and a native plugin (for some tablet brands).
                <ol>
                    <li>Install <a target="_blank" href="https://chrome.google.com/webstore/detail/stylus-pressure/pgdjcdcofllhdgocpjbpfkpmgekhocfh">this Stylus Pressure extension</a> from the Chrome store.</li>
                    <li>Visit the oekaki. Click the red stylus icon in the toolbar and download the native plugin installer. Alternatively, you can download the native plugin <a href="/oekaki/StylusPressurePlugin.msi">here</a> as well.</li>
                    <li>Install the native plugin, then refresh the oekaki page. The icon should no longer be red.</li>
                </ol>
            </p>
            <p>
                <strong>Internet Explorer/Edge</strong><br />
                Pen pressure support is built in to Windows and the latest Internet Explorer/Edge browser. This should support many major tablet brands.
            </p>
        </div>
    </div>
{% endblock %}
