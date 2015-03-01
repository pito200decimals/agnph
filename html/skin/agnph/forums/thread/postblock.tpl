{# Knows about variable named 'post' #}
<div>
    <a name="p{{ post.PostId }}" />
    {% if post.new %}<a name="new" />{% endif %}
    {{ post.poster.DisplayName }} writes:<br />
    <small>(Posted {{ post.PostDate }}, Edited {{ post.PostDate }})</small>
    <div style="margin-left: 20px; margin-bottom: 20px;">
        {{ post.Content }}
    </div>
</div>
