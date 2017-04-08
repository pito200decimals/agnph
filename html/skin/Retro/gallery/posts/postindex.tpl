{% extends 'gallery/posts/postindex-base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/retro-postindex-style.css') }}" />
{% endblock %}

{% block extra_section_nav_items %}
    <li><a href="" onclick="return OpenSlideshow();">Slideshow</a></li>
    <li id="toggle-mobile-container"><a id="toggle-mobile-layout" href="" onclick="return ToggleMobile();">Toggle Mobile</a></li>
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script>
        $(document).ready(function() {
            {# Enable toggle-mobile button if javascript is enabled #}
            $("#toggle-mobile-container").show();
        });
    </script>
{% endblock %}

{% block sidepanel %}
{% endblock %}
