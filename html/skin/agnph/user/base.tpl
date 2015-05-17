{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
{% endblock %}

{% block section_navigation %}
    <ul class="section-nav">
        <li><a href="/user/{{ profile.user.UserId }}/">Profile</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/forums/">Forums</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/gallery/">Gallery</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/fics/">Fics</a></li>
        <li><a href="/user/{{ profile.user.UserId }}/oekaki/">Oekaki</a></li>
    </ul>
{% endblock %}

{% block content %}
    <div class="userpage">
        <div class="userpage-container">
            <div class="profile-sidepanel">
                <div class="sidepanel-section">
                    {% if profile.user.Avatar|length > 0 %}
                        {# avatar image #}
                        <img class="avatarimg" src="{{ profile.user.Avatar }}" />
                    {% else %}
                        {# default avatar image #}
                        <img class="avatarimg" src="http://i.imgur.com/CKd8AGC.png" />
                    {% endif %}
                </div>
                {# Other actions to perform on this user #}
                <div class="sidepanel-section">
                    <h4>Actions</h4>
                    <ul>
                        <li>Send a Message</li>
                    </ul>
                </div>
            </div>
            <div class="profile-content">
                {% block usercontent %}
                {% endblock %}
            </div>
        </div>
    </div>
{% endblock %}
