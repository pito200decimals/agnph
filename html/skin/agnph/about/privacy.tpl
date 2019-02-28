{% extends "about/skin-base.tpl" %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/about/style.css')|raw }}
{% endblock %}

{% block content %}
    <h3>Privacy Policy</h3>
    <div class="block">
        <div class="header">Introduction</div>
        <div class="content">
            <p>
                This document discloses the privacy policy of the website AGNPH (<a href="https://agn.ph/">agn.ph</a>), what data we collect, and what this data is used for.
            </p>
        </div>
    </div>
    <div class="block">
        <div class="header">Data Collection and Use</div>
        <div class="content">
            <p>
                AGNPH collects only enough data from users in order to run the site. This information includes personal information like:
            </p>
            <ul>
                <li>Email Address</li>
                <li>Birthdate</li>
                <li>IP Address</li>
            </ul>
            <p>
                We only use this information to make the site function, such as to recover lost accounts, restrict access to minors, and enforce site bans. This data is only accessible to site admins for the purpose of maintaining and/or fixing the site and is not disclosed to any third parties.
            </p>
        </div>
    </div>
    <div class="block">
        <div class="header">Your Information Controls</div>
        <div class="content">
            <p>
                You can control the public visibility of your information on your account settings. If you have any specific questions about your data or requests for deletion, please contact a <a href="/about/staff/">site admin</a>.
            </p>
        </div>
    </div>
{% endblock %}
