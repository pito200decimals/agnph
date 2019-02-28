{% extends 'fics/tag_help-base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/no-right-panel-style.css')|raw }}
{% endblock %}