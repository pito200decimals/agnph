{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/poolindex-style.css" />
{% endblock %}

{% block gallerycontent %}
    <div class="mainpanel">
        <h3>Pools</h3>
        {% if pools|length > 0 %}
            {# Display search index. #}
            <table class="pooltable">
                <tr>
                    <td><strong>Name</strong></td>
                    <td><strong>Number of Posts</strong></td>
                </tr>
                {% for pool in pools %}
                    <tr>
                        <td><a href="/gallery/post/?search=pool%3A{{ pool.PoolId }}">{{ pool.Name }}</a></td>
                        <td>{{ pool.count }}</td>
                    </tr>
                {% endfor %}
            </table>
            <div class="Clear">&nbsp;</div>
            <div class="indexIterator">
                {% autoescape false %}
                {{ postIterator }}
                {% endautoescape %}
            </div>
        {% else %}
            {# No posts here. #}
            No pools found.
        {% endif %}
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
