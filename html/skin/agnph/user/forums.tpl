{% extends "user/skin-base.tpl" %}

{% block styles %}
    {{ parent() }}
    <style>
    </style>
{% endblock %}

{% block sidebar %}
    {% if user and adminLinks|length > 0 %}
        {{ parent() }}
    {% endif %}
{% endblock %}
{% block sidebar_actions %}
    <ul>
        {{ block('admin_link_block') }}
    </ul>
{% endblock %}

{% block usercontent %}
    <div class="infoblock">
        <h3>Forums Statistics</h3>
        <ul class="basic-info">
            <li><span class="basic-info-label">Forum Posts:</span><span>{{ profile.user.numForumPosts }}</span></li>
            <li><span class="basic-info-label">Threads Started:</span><span>{{ profile.user.numThreadsStarted }}</span></li>
        </ul>
    </div>
{% endblock %}
