{% extends 'base.tpl' %}

{% block styles %}
<link rel="stylesheet" type="text/css" href="{{ skinDir }}/forums/style.css" />
{% endblock %}

{% block scripts %}
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: "textarea",
            plugins: [ "paste", "link", "autoresize", "hr", "wordcount", "code", "contextmenu", "emoticons", "fullscreen", "preview", "image", "searchreplace", "textcolor" ],
            target_list: [ {title: 'New page', value: '_blank'} ],
            toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code fullscreen preview",
            contextmenu: "image link | hr",
            autoresize_max_height: 500
        });
    </script>
{% endblock %}

{% block content %}
    {% if editorForm %}
        {% if crumbs %}{% autoescape false %}{{ crumbs }}{% endautoescape %}{% endif %}
        <h3>{{ formTitle }}</h3>
        {% autoescape false %}
            {{ editorForm }}
        {% endautoescape %}
        {% if crumbs %}{% autoescape false %}{{ crumbs }}{% endautoescape %}{% endif %}
        <h3>{{ postsTitle }}</h3>
        {% if posts %}
            {% for post in posts %}
                {% include 'forums/thread/postblock.tpl' %}
            {% endfor %}
        {% endif %}
    {% elseif content %}
        {{ content }}
    {% else %}
        No content here.
    {% endif %}
{% endblock %}
