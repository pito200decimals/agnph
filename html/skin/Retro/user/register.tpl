{% extends 'user/register-base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/no-left-panel-mobile-style.css')|raw }}
    <style>
    @media only handheld, screen and (max-device-width: 820px), screen and (max-width: 820px) {
        .captcha-offset {
            margin-left: inherit;
        }
    }
    </style>
{% endblock %}
