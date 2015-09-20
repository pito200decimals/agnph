{% extends "user/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <style>
    </style>
{% endblock %}

{% block sidebar %}
    {% if user and adminLinks|length > 0 %}
        <h4>Actions</h4>
        <ul>
            {{ block('admin_link_block') }}
        </ul>
    {% endif %}
{% endblock %}

{% use "fics/storyblock.tpl" %}

{% block usercontent %}
    <div class="infoblock">
        <h3>Fics Statistics</h3>
        <ul id="basic-info">
            <li><span class="basic-info-label">Forum Posts:</span><span>{{ profile.user.numForumPosts }}</span></li>
            <li><span class="basic-info-label">Threads Started:</span><span>{{ profile.user.numThreadsStarted }}</span></li>
        </ul>
    </div>
{% endblock %}
