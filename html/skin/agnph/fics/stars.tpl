{% macro stars(story) %}
    {% for star in story.stars %}
        {% if star == "half" %}
            <img src='/images/starhalf.gif' />
        {% elseif star == "full" %}
            <img src='/images/star.gif' />
        {% endif %}
    {% endfor %}
{% endmacro %}