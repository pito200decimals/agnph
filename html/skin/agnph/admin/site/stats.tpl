{% extends 'admin/skin-base.tpl' %}

{% block sub_section_navigation %}
    <li><a href="/admin/">Settings</a></li>
    <li class="selected-admin-tab"><a href="/admin/stats/">Stats</a></li>
    <li><a href="/admin/log/">Log</a></li>
{% endblock %}

{% block styles %}
    {{ parent() }}
    <style>
        .strike {
            text-decoration: line-through;
        }
    </style>
{% endblock %}

{% block content %}
    <h3>Site Visit Stats</h3>
    {{ block('banner') }}
    <a href="/admin/stats/">Current Stats</a>
    <a href="/admin/stats/?duration=day">Day Stats</a>
    <table>
        <thead>
            <tr>
                <td>
                    Page
                </td>
                <td>
                    Visit Count
                </td>
            </tr>
        </thead>
        <tbody>
            {% for stat in stats %}
            <tr>
                <td>
                    {% if not stat.Blacklisted %}
                        <a href="{{ stat.PageUrl }}">{{ stat.PageUrl }}</a>
                    {% else %}
                        <span class="strike">{{ stat.PageUrl }}</span>
                    {% endif %}
                </td>
                <td>
                    {% if not stat.Blacklisted %}
                        <a href="{{ stat.PageUrl }}">{{ stat.Count }}</a>
                    {% else %}
                        <span class="strike">{{ stat.Count }}</span>
                    {% endif %}
                </td>
            </tr>
            {% endfor %}
        </tbody>
    </table>
{% endblock %}
