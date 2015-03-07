{% spaceless %}
<!DOCTYPE html>
<html>
    <head>
        {% block head %}
            <title>AGNPH</title>
        {% endblock %}
        {% block scripts %}
        {% endblock %}
        <link rel="stylesheet" type="text/css" href="{{ skinDir }}/style.css" />
        {% block styles %}
        {% endblock %}
    </head>
    <body>
        <div id="mainbody">
            <div id="header">
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
            </div>
            <hr />
            <div id="content">
                {% if error_msg %}
                    <div class="error">
                        <p>{{ error_msg }}</p>
                    </div>
                {% else %}
                    {% block content %}
                        {% if error_msg %}
                            {{ error_msg }}
                        {% elseif content %}
                            {{ content }}
                        {% else %}
                            No content here.
                        {% endif %}
                    {% endblock %}
                {% endif %}
            </div>
        </div>
        <div id="footer">
            {% block footer %}
                <span>Copyright AGNPH 2015</span>
            {% endblock %}
        </div>
    </body>
</html>
{% endspaceless %}