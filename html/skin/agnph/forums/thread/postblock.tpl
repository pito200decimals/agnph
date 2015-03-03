{# Knows about variable named 'post' corresponding mostly to the database row for that post. #}
<div style="border-color:black;border-width:1px;border-style:solid;margin:5px;padding:5px;">
    <a name="p{{ post.PostId }}" />
    {{ post.poster.DisplayName }} writes:<br />
    {% if post.PostDate %}
        <small>(Posted {{ post.PostDate }}
                {% if post.EditDate %}, Edited {{ post.EditDate }}{% endif %}
            )</small>
    {% endif %}
    <div style="margin-left: 20px; margin-bottom: 20px;">
        {% if post.quoteLink %}
            <a href="{{ post.quoteLink }}" style="margin:5px;">Quote</a>
        {% endif %}
        <br />
        {% autoescape false %}
        {{ post.Content }}
        {% endautoescape %}
        <hr />
        {{ post.poster.Signature }}
    </div>
</div>
