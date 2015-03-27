{% extends 'base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/forums/style.css" />
{% endblock %}

{% block scripts %}
    {% if user.UserId > 0 %}
        <script type="text/javascript">
            function deletePost(pid) {
                if (!confirm('Are you sure you want to delete this post?')) return false;
                document.getElementById('postfield').value = pid;
                document.getElementById('deleteform').submit();
                return false;
            }
        </script>
    {% endif %}
{% endblock %}

{% block content %}
    {% if thread %}
        {% if user.UserId > 0 %}
            <form id="deleteform" method="POST" action="/forums/delete/">
                <input id="postfield" type="hidden" name="post" value="-1" />
                <input type="hidden" name="hash" value="{{ deletehash }}" />
            </form>
        {% endif %}
        <span class="crumbs">{% autoescape false %}{{ crumbs }}{% endautoescape %}</span><br />
        <div style="margin: 10px;">
            <h3>Thread Title: {{ thread.Title }}</h3>
            <small>Thread posted by {{ thread.creator.DisplayName }}</small>
        </div>
        {% if thread.Posts %}
            {% autoescape false %}
                <span style="margin:15px;">
                    Pages: {{ page_iterator }}
                </span>
            {% endautoescape %}<br />
            {% if user.canPostToThread %}
                {% block threadactions %}
                    <a href="/forums/reply/{{ thread.PostId }}/">Reply</a>
                {% endblock %}
            {% endif %}
            {% for post in thread.Posts %}
                {% if post.new %}<a name="new" />{% endif %}
                {% include 'forums/thread/postblock.tpl' %}
            {% endfor %}
            {% if user.canPostToThread %}
                {{ block('threadactions') }}
            {% endif %}
            {% autoescape false %}
                <span style="margin:15px;">
                    Pages: {{ page_iterator }}
                </span>
            {% endautoescape %}
        {% endif %}
        <span class="crumbs">{% autoescape false %}{{ crumbs }}{% endautoescape %}</span><br />
    {% elseif content %}
        {{ content }}
    {% else %}
        No content here.
    {% endif %}
{% endblock %}
