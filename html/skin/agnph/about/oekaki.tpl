{% extends "about/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
{% endblock %}

{% block content %}
    <h3>AGNPH Help Pages (Oekaki)</h3>
    <div class="block">
        <div class="header">Where'd the Oekaki Go?</div>
        <div class="content">
            <p>
                AGNPH used to have an oekaki section before the recent site move. However, the software that allowed artists to draw in the browser (Java)
                is no longer supported by many modern browsers. For now, the section has been archived, although we are actively exploring replacement software.
                When technical issues are resolved, it will be re-opened for everyone to use.
            </p>
        </div>
    </div>
    <div class="block">
        <div class="header">What about my Oekaki account?</div>
        <div class="content">
            <p>
                If you used to have an oekaki account, don't fear! It's still on the site, and you can log in with it as normal (or import/merge it into your existing AGNPH account).
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
                <small><strong>Note:</strong> Native plugin support may be removed from Firefox later in 2016.</small>
            </p>
            <p>
                <strong>Google Chrome</strong><br />
                Pen pressure in Chrome is supported via chrome extension and a native plugin (for some tablet brands).
                <ol>
                    <li>Install <strong><a target="_blank" href="https://chrome.google.com/webstore/detail/stylus-pressure/pgdjcdcofllhdgocpjbpfkpmgekhocfh">this Stylus Pressure extension</a></strong> from the Chrome store.</li>
                    <li>Visit the oekaki. Click the red stylus icon in the toolbar and download the native plugin installer. Alternatively, you can download the native plugin <a href="/oekaki/StylusPressurePlugin.msi">here</a> as well.</li>
                    <li>Install the native plugin, then refresh the oekaki page. The icon should no longer be red.</li>
                </ol>
            </p>
            <p>
                <strong>Internet Explorer</strong><br />
                Pen pressure support is built in to Windows and Internet Explorer, and should support major tablet brands.
            </p>
        </div>
    </div>
{% endblock %}
