{% extends "user/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <style>
        a.thumb-link:hover {
            text-decoration: none;
        }
        .oekaki-slot-container {
            display: inline-block;
            margin: 15px;
            text-align: center;
        }
        .oekaki-thumb-container {
            display: inline-block;
            max-width: 150px;
            max-height: 150px;
            border-radius: 10px;
            border: 1px solid black;
            overflow: hidden;
        }
        .oekaki-thumb-container img {
            display: block;
            max-width: 150px;
            max-height: 150px;
        }
        .oekaki-slot-label {
            display: inline-block;
        }
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
    {% if slots|length > 0 %}
        <div class="infoblock">
            <h3>Drawings in Progress (Private)</h3>
            {% for slot in slots %}
                <a class="thumb-link" target="_blank" href="{{ slot.href }}">
                    <div class="oekaki-slot-container">
                        <div>
                            <div class="oekaki-thumb-container">
                                <img src="{{ slot.thumb }}" />
                            </div>
                        </div>
                        <div>
                            <div class="oekaki-slot-label">
                                {{ slot.name }}<br />
                                <small>{{ slot.duration }}</small>
                            </div>
                        </div>
                    </div>
                </a>
            {% endfor %}
        </div>
    {% endif %}
{% endblock %}
