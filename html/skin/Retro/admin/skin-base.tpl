{% extends 'admin/base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/no-left-panel-mobile-style.css')|raw }}
    {{ inline_css_asset('/no-right-panel-style.css')|raw }}
{% endblock %}

{% block page_title_bar %}
<strong>AGNPH Administration Panel</strong>
{% endblock %}