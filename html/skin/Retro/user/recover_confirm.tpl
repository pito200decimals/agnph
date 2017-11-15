{% extends 'user/recover_confirm-base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/no-left-panel-mobile-style.css') }}" />
{% endblock %}
