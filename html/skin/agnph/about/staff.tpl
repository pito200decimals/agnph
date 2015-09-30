{% extends "about/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
    <style>
        #content ul {
            list-style: none;
        }
    </style>
{% endblock %}

{% block content %}
    <h3>AGNPH Staff</h3>
    <div class="block">
        <div class="header">Site Owners</div>
        <div class="content">
            <ul>
                <li>Flygon</li>
                <li>Wilon</li>
            </ul>
        </div>
    </div>
    <div class="block">
        <div class="header">Site Administrators</div>
        <div class="content">
            <ul>
                <li>Cyn</li>
                <li>HatchlingByHeart</li>
            </ul>
        </div>
    </div>
    <div class="block">
        <div class="header">Section Admins and Moderators</div>
        <div class="content">
            <ul>
                <li>Smbcha (Gallery)</li>
                <li>Alynna (IRC)</li>
                <li>Kupok (IRC)</li>
                <li>Lieger (Minecraft)</li>
            </ul>
        </div>
    </div>
{% endblock %}
