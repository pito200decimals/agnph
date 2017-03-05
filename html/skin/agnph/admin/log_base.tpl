{% extends 'admin/skin-base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
    <style>
        .action-bar {
            margin: 5px;
        }
        .action-bar ul {
            list-style: none;
            padding: 0px;
        }
        .action-bar ul li {
            float: right;
        }
    </style>
{% endblock %}

{% block content %}
    <h3>{{ sectionName }} Administrator Logs</h3>
    {{ block('banner') }}
    <div class="action-bar">
        <ul>
            <li><a href="{{ verboseLink }}">Verbose</a></li>
        </ul>
    </div>
    <table class="list-table">
        <thead>
            <tr>
                <td><strong>Time</strong></td>
                <td><strong>Action</strong></td>
                <td><strong>Section</strong></td>
            </tr>
        </thead>
        <tbody>
            {% if log|length > 0 %}
                {% for entry in log %}
                    <tr>
                        <td>{{ entry.date }}</td>
                        <td>{% autoescape false %}{{ entry.Action }}{% endautoescape %}</td>
                        <td>{{ entry.section }}</td>
                    </tr>
                {% endfor %}
            {% else %}
                <tr>
                    <td></td>
                    <td colspan="2">No logs found</td>
                </tr>
            {% endif %}
        </tbody>
    </table>
    <div class="iterator">
        {% autoescape false %}{{ iterator }}{% endautoescape %}
    </div>
{% endblock %}
