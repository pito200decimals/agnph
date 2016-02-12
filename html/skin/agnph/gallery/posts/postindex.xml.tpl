<?xml version="1.0" encoding="UTF-8"?>
<posts count="{{ total_num_posts }}" offset="{{ offset }}">
    {% for post in posts %}
        <post>
            <id type="integer">{{ post.PostId }}</id>
            {# tags #}
            {# description #}
            <created_at type="timestamp">{{ post.DateUploaded }}</created_at>
            <creator_id type="integer">{{ post.UploaderId }}</creator_id>
            {# author #}
            {% if post.Source %}<source>{{ post.Source }}</source>{% endif %}
            {# score #}
            <fav_count type="integer">{{ post.NumFavorites }}</fav_count>
            {% if post.Status != 'D' %}
                <md5>{{ post.Md5 }}</md5>
                {# file_size #}
                <file_url>http://agn.ph{{ post.downloadUrl }}</file_url>
                <file_ext>{{ post.Extension }}</file_ext>
                <thumbnail_url>http://agn.ph{{ post.thumbnail }}</thumbnail_url>
                {# thumbnail width/height #}
                {% if post.preview %}<preview_url>http://agn.ph{{ post.preview }}</preview_url>{% endif %}
                {# preview width/height #}
            {% endif %}
            <rating>{{ post.Rating }}</rating>
            <has_children type="boolean">
                {% if post.outlineClass == "parentoutline" %}
                    true
                {% else %}
                    false
                {% endif %}
            </has_children>
            {# children #}
            {% if post.ParentPostId == -1 %}
                <parent_id nil="true" />
            {% else %}
                <parent_id type="integer">{{ post.ParentPostId }}</parent_id>
            {% endif %}
            <status>
                {% if post.Status == 'P' %}
                    pending
                {% elseif post.Status == 'A' %}
                    approved
                {% elseif post.Status == 'F' %}
                    flagged
                {% elseif post.Status == 'D' %}
                    deleted
                {% else %}
                    unknown
                {% endif %}
            </status>
            <width type="integer">{{ post.Width }}</width>
            <height type="integer">{{ post.Height }}</height>
            <num_comments type="integer">{{ post.NumComments }}</num_comments>
            {# has_notes #}
            {# artist #}
            {# sources #}
        </post>
    {% endfor %}
</posts>
