{% extends 'admin/base.tpl' %}

{% block scripts %}
    {{ parent() }}
    <script>
        var TYPE_LIST = {% block type_list %}[]{% endblock %};
        var SECTION = "{{ section }}";
    </script>
    <script src="{{ asset('/admin/tags/tags.js') }}"></script>
{% endblock %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/admin/tags/style.css') }}" />
{% endblock %}

{% block content %}
    <h3>{% block section %}[Section]{% endblock %} Tag Console</h3>
    <p>Search: <input id="search" type="text" />
        <input id="tag-filter" name="filter" type="radio" checked />Tags
        <input id="alias-filter" name="filter" type="radio" />Aliases
        <input id="implication-filter" name="filter" type="radio" />Implications
        <input id="create-filter" type="button" value="Create New Tag" />
        <input id="update-tag-counts" type="button" value="Update Tag Counts" /></p>
    <p><small id="searching-span">Searching...</small><small id="processing-span">Processing...</small></p>
    <select id="tag-list" size="10" style="width: 100%;">
    </select>
    <div id="tag-container">
    </div>
{% endblock %}
