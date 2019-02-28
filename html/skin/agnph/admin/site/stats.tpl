{% extends 'admin/skin-base.tpl' %}

{% block sub_section_navigation %}
    <li><a href="/admin/">Settings</a></li>
    <li class="selected-admin-tab"><a href="/admin/stats/">Stats</a></li>
    <li><a href="/admin/log/">Log</a></li>
{% endblock %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/list-style.css')|raw }}
    <style>
        .strike {
            text-decoration: line-through;
        }
        .validated {
            font-weight: bold;
        }
    </style>
{% endblock %}

{% block content %}
    <h3>Site Visit Stats</h3>
    {{ block('banner') }}
    <ul>
        <li><a href="/admin/stats/">Current Stats</a></li>
        <li><a href="/admin/stats/?duration=day">Day Stats</a></li>
        <li><a href="/admin/stats/?type=useragent">Current Stats (User Agent)</a></li>
        <li><a href="/admin/stats/?type=useragent&duration=day">Day Stats (User Agent)</a></li>
    </ul>
    <table class="list-table">
        <thead>
            <tr>
                <td>
                    {{ column_type }}
                </td>
                <td>
                    Visit Count
                </td>
                <td>
                    Blacklist Reason
                </td>
            </tr>
        </thead>
        <tbody>
            {% for stat in stats %}
            <tr>
                <td>
                    <span class="{% if stat.Blacklisted %}strike{% endif%} {% if stat.validated %}validated{% endif %}">
                        {% if stat.PageUrl and not stat.Blacklisted %}
                            <a href="{{ stat.PageUrl }}">{{ stat.Value }}</a>
                        {% else %}
                            {{ stat.Value }}
                        {% endif %}
                    </span>
                </td>
                <td>
                    <span class="{% if stat.Blacklisted %}strike{% endif%} {% if stat.validated %}validated{% endif %}">
                        {{ stat.Count }}
                    </span>
                </td>
                <td>
                    {% if stat.Blacklisted %}
                        {{ stat.Reason }}
                    {% endif %}
                </td>
            </tr>
            {% endfor %}
        </tbody>
    </table>
{% endblock %}
