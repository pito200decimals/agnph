{# Knows about variable named 'post' corresponding mostly to the database row for that post. #}
<div style="border-color:black;border-width:1px;border-style:solid;margin:5px;padding:5px;">
    <a name="p{{ post.PostId }}" />
    <strong>{{ post.poster.DisplayName }} writes:</strong><br />
    <em>{{ post.Title }}</em><br />
    {% if post.PostDate %}
        <small>(Posted {{ post.PostDate }}
                {% if post.EditDate %}, Edited {{ post.EditDate }}{% endif %}
            )</small>
    {% endif %}
    <div style="margin-left: 20px; margin-bottom: 20px;">
        {% if post.modifyLink %}<a href="{{ post.modifyLink }}">Modify</a>{% endif %}
        {% if post.deleteLink %}<a href="{{ post.deleteLink }}">Delete</a>{% endif %}
        <div id="p{{ post.PostId }}">
            {% autoescape false %}
            {{ post.Content }}
            {% endautoescape %}
        </div>
        <hr />
        {{ post.poster.Signature }}
    </div>
</div>
