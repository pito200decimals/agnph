{% extends 'forums/base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/forums/retro-style.css')|raw }}
    {{ inline_css_asset('/no-left-panel-mobile-style.css')|raw }}
    {{ inline_css_asset('/no-right-panel-style.css')|raw }}
{% endblock %}

{% block page_title_bar %}
    <strong>AGNPH Forums</strong>
{% endblock %}

{% block breadcrumb_bar %}
    <ul class="forums-breadcrumb-bar font-scalable">
        {{ block('breadcrumb_block_recursive') }}
    </ul>
{% endblock %}

{% block section_navigation %}
{% endblock %}
