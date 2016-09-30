{% extends 'gallery/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
    <style>
        .tag-edit {
            margin: 2px;
            display: inline-block;
        }
    </style>
{% endblock %}

{% block content %}
    <div class="mainpanel">
        <h3>Tag History</h3>
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
                {% if tagHistoryItems|length > 0 %}
                    {% for item in tagHistoryItems %}
                        <tr>
                            <td>{{ item.date }}</td>
                            <td><a href="/user/{{ item.user.UserId }}/gallery/">{{ item.user.DisplayName }}</a></td>
                            <td>{% autoescape false %}{{ item.tagChanges }}{% endautoescape %}</td>
                        </tr>
                    {% endfor %}
                {% else %}
                    <tr>
                        <td></td>
                        <td colspan="2">No tag history found</td>
                    </tr>
                {% endif %}
            </tbody>
        </table>
        <div class="Clear">&nbsp;</div>
        <div class="iterator">
            {% autoescape false %}{{ postIterator }}{% endautoescape %}
        </div>
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
