{% extends 'user/recover_confirm-base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/no-left-panel-mobile-style.css')|raw }}
{% endblock %}
