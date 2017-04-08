{% extends 'forums/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/forums/retro-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/no-left-panel-mobile-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/no-right-panel-style.css') }}" />
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
