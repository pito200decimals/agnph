{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/list-style.css" />
    <style>
        .tag-edit {
            margin: 5px;
        }
    </style>
{% endblock %}

{% block content %}
    <div class="mainpanel">
        <h3>Tag History</h3>
        {% if tagHistoryItems|length > 0 %}
            {# Display tag history index. #}
            <table class="list-table">
                <thead>
                    <tr>
                        <td><strong>Date</strong></td>
                        <td><strong>Edited by</strong></td>
                        <td><strong>Tag Changes</strong></td>
                    </tr>
                </thead>
                <tbody>
                    {% for item in tagHistoryItems %}
                        <tr>
                            <td>{{ item.date }}</td>
                            <td><a href="/user/{{ item.user.UserId }}/gallery/">{{ item.user.DisplayName }}</a></td>
                            <td>{% autoescape false %}{{ item.tagChanges }}{% endautoescape %}</td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
            <div class="Clear">&nbsp;</div>
            <div class="iterator">
                {% autoescape false %}
                {{ postIterator }}
                {% endautoescape %}
            </div>
        {% else %}
            {# No history items here. #}
            No tag history found.
        {% endif %}
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
