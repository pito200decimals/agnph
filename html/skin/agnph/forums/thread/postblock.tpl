{# Knows about variable named 'post' #}
<div>
    <a name="p{{ post.PostId }}" />
    {% if post.new %}<a name="new" />{% endif %}
    {{ post.poster.DisplayName }} writes:<br />
    <small>(Posted {{ post.PostDate }}{% if post.EditDate %}, Edited {{ post.EditDate }}{% endif %})</small>
    <div style="margin-left: 20px; margin-bottom: 20px;">
        {{ post.Content }}
        <hr />
        {{ post.poster.Signature }}
    </div>
</div>
