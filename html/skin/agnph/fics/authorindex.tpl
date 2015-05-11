{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/authorindex-style.css" />
{% endblock %}

{% block ficscontent %}
    <div class="mainpanel">
        <h3>Authors</h3>
        <form action="/fics/authors/" accept-charset="UTF-8">
            <label>Search for Authors:</label><input class="search" name="prefix" type="text" value="{{ searchPrefix }}" required/>
        </form>
        {% if authors|length > 0 %}
            {# Display search index. #}
            <table class="authortable">
                <thead>
                    <tr>
                        <td><div><strong>Name</strong></div></td>
                        <td><div><strong>Number of Stories</strong></div></td>
                    </tr>
                </thead>
                <tbody>
                    {% for author in authors %}
                        <tr>
                            <td><div><a href="/user/{{ author.UserId }}/fics/">{{ author.DisplayName }}</a></div></td>
                            <td><div>{{ author.storyCount }}</div></td>
                        </tr>
                    {% endfor %}
                </tbody>
            </table>
            <div class="Clear">&nbsp;</div>
            <div class="indexIterator">
                {% autoescape false %}
                {{ iterator }}
                {% endautoescape %}
            </div>
        {% else %}
            {# No tags here. #}
            No tags found.
        {% endif %}
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
