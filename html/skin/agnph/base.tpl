{% spaceless %}
{# Define blocks to be used elsewhere #}
{% if false %}
    {# Notification banners #}
    {% block banner %}
        {% for notification in banner_nofications %}
            <div class="banner-nofication{% for class in notification.classes %} {{ class }}{% endfor %}">
                <p>
                    {% if notification.strong %}<strong>{% endif %}
                        {% if notification.noescape %}
                            {% autoescape false %}{{ notification.text }}{% endautoescape %}
                        {% else %}
                            {{ notification.text }}
                        {% endif %}
                    {% if notification.strong %}</strong>{% endif %}
                    {% if notification.dismissable %}
                        <input type="button"onclick="$(this).parent().parent().hide();" value="X" />
                    {% endif %}
                </p>
            </div>
        {% endfor %}
    {% endblock %}
{% endif %}

{# Main site base template #}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        {% block head %}
            <title>AGNPH</title>
        {% endblock %}
        {% block scripts %}
        {% endblock %}
        <link rel="stylesheet" type="text/css" href="{{ skinDir }}/style.css" />
        {% block styles %}
        {% endblock %}
        {% if debug %}{# TODO: Remove #}
            <style type="text/css">
                div,span,li {
                    border-style: dotted;
                    border-width: 1px;
                }
            </style>
        {% endif %}
    </head>
    <body>
        <div id="mainbody">
            <div id="header">{# TODO: Replace with actual site banner #}
                {% block welcome %}
                    <h1>Welcome, {% if user %}{{ user.DisplayName }}{% else %}Guest{% endif %}!</h1>
                {% endblock %}
                <hr />
                {% block main_navigation %}
                    <ul class="navigation">
                        {% for item in navigation %}
                            <li{% if item.highlight %} class="selected-nav"{% endif %}><a href="{{ item.href }}">{{ item.caption }}</a></li>
                        {% endfor %}
                        {% for item in account_links %}
                           <li{% if item.highlight %} class="selected-nav"{% endif %}><a href="{{ item.href }}">{{ item.caption }}</a></li>
                        {% endfor %}
                    </ul>
                {% endblock %}
                <hr />
                {% block section_navigation %}
                {% endblock %}
            </div>
            <div class="Clear">&nbsp;</div>
            <div id="content">
                {% if error_msg %}
                    <div class="error-box">
                        <p class="error-msg">
                            {{ error_msg }}
                        </div>
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
            <div class="Clear" style="display: block">&nbsp;</div>
        </div>
        <div id="footer">
            {% block footer %}
                <span><small>Copyright AGNPH 2015</small></span>
            {% endblock %}
        </div>
    </body>
</html>
{% endspaceless %}