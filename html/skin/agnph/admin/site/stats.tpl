{% extends 'admin/skin-base.tpl' %}

{% block sub_section_navigation %}
    <li><a href="/admin/">Settings</a></li>
    <li class="selected-admin-tab"><a href="/admin/stats/">Stats</a></li>
    <li><a href="/admin/log/">Log</a></li>
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
                    {{ stat.PageUrl }}
                </td>
                <td>
                    {{ stat.Count }}
                </td>
            </tr>
            {% endfor %}
        </tbody>
    </table>
{% endblock %}
