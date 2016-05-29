{% extends 'fics/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
{% endblock %}

{% block content %}
    <h3>Authors</h3>
    <div class="list-search-bar">
        <form action="/fics/authors/" accept-charset="UTF-8">
            <div class="search">
                <input class="search" name="search" value="{{ searchTerms }}" type="text" required placeholder="Search Authors" />
                <input type="submit" class="search-button" value="" />
            </div>
        </form>
    </div>
    {# Display search index. #}
    <table class="list-table">
        <thead>
            <tr>
                <td><strong>Name</strong></td>
                <td><strong>Number of Stories</strong></td>
            </tr>
        </thead>
        <tbody>
            {% if authors|length > 0 %}
                {% for author in authors %}
                    <tr>
                        <td><a href="/user/{{ author.UserId }}/fics/">{{ author.DisplayName }}</a></td>
                        <td>{{ author.storyCount }}</td>
                    </tr>
                {% endfor %}
            {% else %}
                <tr>
                    <td>No Authors Found</td>
                    <td></td>
                </tr>
            {% endif %}
        </tbody>
    </table>
    <div class="Clear">&nbsp;</div>
    <div class="iterator">
        {% autoescape false %}{{ iterator }}{% endautoescape %}
    </div>
{% endblock %}
