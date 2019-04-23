{% extends "about/skin-base.tpl" %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/about/style.css')|raw }}
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
                To enable pen support, type "about:config" into the address bar and search and enable the following flags:
                <ul>
                    <li>dom.w3c_pointer_events.enabled</li>
                    <li>dom.w3c_pointer_events.dispatch_by_pointer_messages</li>
                </ul>
                Once those are enabled, pen/tablet devices should work properly with pressure detection.
            </p>
            <p>
                <strong>Google Chrome</strong><br />
                Pen pressure in natively supported in Chrome.
            </p>
            <p>
                <strong>Internet Explorer/Edge</strong><br />
                Pen pressure support is built in to Windows and the latest Internet Explorer/Edge browser. This should support many major tablet brands.
            </p>
        </div>
    </div>
{% endblock %}
