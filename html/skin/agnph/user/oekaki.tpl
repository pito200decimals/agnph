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

{% block usercontent %}
    <div class="infoblock">
        <h3>Oekaki Statistics</h3>
        <ul id="basic-info">
            <li><span class="basic-info-label">Image Posts:</span><span>{{ profile.user.numOekakiImagePosts }}</span></li>
            <li><span class="basic-info-label">Comments:</span><span>{{ profile.user.numComments }}</span></li>
        </ul>
    </div>
{% endblock %}
