{% spaceless %}
<!DOCTYPE html>
<html>
    <head>
        {% block head %}
            <title>AGNPH</title>
        {% endblock %}
    </head>
    <body>
        {% block welcome %}
            <h1>Welcome, {{ user.DisplayName }}!</h1>
        {% endblock %}
        <hr />
        {% block navigation %}
            <ul id="navigation">
                {% for item in navigation %}
                    <li><a href="{{ item.href }}">{{ item.caption }}</a></li>
                {% endfor %}
            </ul>
            <ul id="account_links">
                {% for item in account_links %}
                    <li><a href="{{ item.href }}">{{ item.caption }}</a></li>
                {% endfor %}
            </ul>
        {% endblock %}
        <hr />
        <div id="content">
            {% block content %}
                {% if error_msg %}
                    {{ error_msg }}
                {% elseif content %}
                    {{ content }}
                {% else %}
                    No content here.
                {% endif %}
            {% endblock %}
        </div>
        <hr />
        <div id="footer">
        {% block footer %}
            Copyright AGNPH 2015
        {% endblock %}
        </div>
    </body>
</html>
{% endspaceless %}