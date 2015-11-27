{% extends "about/base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/about/style.css') }}" />
{% endblock %}

{% block content %}
    <h3>AGNPH Help Pages (Oekaki)</h3>
    <div class="block">
        <div class="header">Where'd the Oekaki Go?</div>
        <div class="content">
            <p>
                AGNPH used to have an oekaki section before the recent site move. However, the software that allowed artists to draw in the browser (Java)
                is no longer supported by many modern browsers. For now, the section has been archived, although we are actively exploring replacement software.
                When technical issues are resolved, it will be re-opened for everyone to use.
            </p>
        </div>
    </div>
    <div class="block">
        <div class="header">What about my Oekaki account?</div>
        <div class="content">
            <p>
                If you used to have an oekaki account, don't fear! It's still on the site, and you can log in with it as normal (or import/merge it into your existing AGNPH account).
            </p>
        </div>
    </div>
{% endblock %}
