{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
{% endblock %}

{% block usercontent %}
    <h2>{{ profile.user.DisplayName }}</h2>
    <div class="infoblock">
        <h3>Bio</h3>
        {% autoescape false %}
            {{ profile.user.bio }}
        {% endautoescape %}
    </div>
    <div class="infoblock">
        <h3>Basic Info</h3>
        <ul>
            <li><span>Admin:</span><span>N/A</span></li>
            <li><span>Species:</span><span>N/A</span></li>
            <li><span>Title:</span><span>N/A</span></li>
            <li><span>Last Active:</span><span>N/A</span></li>
            <li><span>Date Registered:</span>N/A<span></span></li>
            <li><span>Forum Posts:</span><span>0</span></li>
            <li><span>Gallery Uploads:</span><span>0</span></li>
            <li><span>Fic Stories:</span><span>0</span></li>
            <li><span>Oekaki Drawn:</span><span>0</span></li>
            <li><span>Email[ADMIN]:</span><span>N/A</span></li>
            <li><span>IP[ADMIN]:</span><span>N/A</span></li>
        </ul>
    </div>
{% endblock %}
