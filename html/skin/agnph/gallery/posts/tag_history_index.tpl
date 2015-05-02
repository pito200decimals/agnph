{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/taghistory-style.css" />
{% endblock %}

{% block gallerycontent %}
    <div class="mainpanel">
        <h3>Tag History</h3>
        {% if tagHistoryItems|length > 0 %}
            {# Display tag history index. #}
            <table class="tag-edit-table">
                <tr>
                    <td><strong>Date</strong></td>
                    <td><strong>Edited by By</strong></td>
                    <td><strong>Tag Changes</strong></td>
                </tr>
                {% for item in tagHistoryItems %}
                    <tr>
                        <td>{{ item.date }}</td>
                        <td><a href="/user/{{ item.user.UserId }}/gallery/">{{ item.user.DisplayName }}</a></td>
                        <td>{% autoescape false %}{{ item.tagChanges }}{% endautoescape %}</td>
                    </tr>
                {% endfor %}
            </table>
            <div class="Clear">&nbsp;</div>
            <div class="indexIterator">
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
