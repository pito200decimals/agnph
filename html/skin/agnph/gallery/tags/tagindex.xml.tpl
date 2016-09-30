<?xml version="1.0" encoding="UTF-8"?>
<tags count="{{ total_num_items }}" offset="{{ offset }}">
    {% for tag in tags %}
        <tag>
            <id type="integer">{{ tag.TagId }}</id>
            <name>{{ tag.Name }}</name>
            <count type="integer">{{ tag.ItemCount }}</count>
            <type type="integer">
                {% if tag.Type == 'A' %}
                    1
                {% elseif tag.Type == 'B' %}
                    3
                {% elseif tag.Type == 'C' %}
                    4
                {% elseif tag.Type == 'D' %}
                    5
                {% elseif tag.Type == 'M' %}
                    0
                {% else %}
                    -1
                {% endif %}
            </type>
            <type_name>
                {% if tag.Type == 'A' %}
                    artist
                {% elseif tag.Type == 'B' %}
                    copyright
                {% elseif tag.Type == 'C' %}
                    character
                {% elseif tag.Type == 'D' %}
                    species
                {% elseif tag.Type == 'M' %}
                    general
                {% else %}
                    unknown
                {% endif %}
            </type_name>
            {% if tag.alias %}
                <preferred_tag>{{ tag.alias }}</preferred_tag>
            {% endif %}
        </tag>
    {% endfor %}
</tags>
