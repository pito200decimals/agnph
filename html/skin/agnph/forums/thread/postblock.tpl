{# Knows about variable named 'post' corresponding mostly to the database row for that post. #}
<div style="border-color:black;border-width:1px;border-style:solid;margin:5px;padding:5px;">
    <a name="p{{ post.PostId }}" />
    {{ post.poster.DisplayName }} writes:<br />
    <small>(Posted {{ post.PostDate }}{% if post.EditDate %}, Edited {{ post.EditDate }}{% endif %})</small>
    <div style="margin-left: 20px; margin-bottom: 20px;">
        {{ post.Content }}
        <hr />
        {{ post.poster.Signature }}
    </div>
</div>
