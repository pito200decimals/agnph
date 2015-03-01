{# Knows about variable named 'post' #}
<div>
    <a name="p{{ post.id }}" />
    {% if post.new %}<a name="new" />{% endif %}
    {{ post.poster.DisplayName }} writes:
    <div style="margin-left: 20px; margin-bottom: 20px;">
        {{ post.content }}
    </div>
</div>
