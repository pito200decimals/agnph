{% extends 'forums/skin-base.tpl' %}

{% block styles %}
    {{ parent() }}
    <style>
        .form-block {
            margin: 5px;
        }
    </style>
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script src="{{ asset('/scripts/tinymce.min.js') }}"></script>
    <script src="{{ asset('/scripts/tinymce-spoiler-plugin.js') }}"></script>
    <script>
        $(document).ready(function() {
            tinymce.init({
                selector: "textarea#compose",
                plugins: [ "paste", "link", "autoresize", "code", "contextmenu", "emoticons", "image", "textcolor", "spoiler" ],
                target_list: [ {title: 'New page', value: '_blank'} ],
                toolbar: "undo redo | bold italic underline | bullist numlist | image link | code blockquote spoiler",
                contextmenu: "image link",
                autoresize_max_height: 300,
                resize: true,
                menubar: false,
                relative_urls: false,
                content_css: "{{ asset('/comments-style.css') }}"
            });
        });
    </script>
{% endblock %}

{% use 'includes/comment-block.tpl' %}

{% block content %}
    {% if post %}
        <h3>Editing post: {{ post.Title }}</h3>
    {% elseif thread %}
        <h3>Replying to thread: {{ thread.Title }}</h3>
    {% elseif board %}
        <h3>Posting to board: {{ board.Name }}</h3>
    {% endif %}
    {{ block('banner') }}
    <form method="POST" accept-encoding="UTF-8">
        <input type="hidden" name="action" value="{{ action }}" />
        <input type="hidden" name="id" value="{{ id }}" />
        <div class="form-block">
            Title: <input type="text" name="title" value="{% if POST.title %}{{ POST.title }}{% elseif post.Title %}{{ post.Title }}{% elseif thread.Title %}RE: {{ thread.Title }}{% endif %}" required />
        </div>
        <div class="form-block">
            <textarea id="compose" name="text">
                {% autoescape false %}
                    {% if POST.text %}
                        {{ POST.text }}
                    {% elseif post.Text %}
                        {{ post.Text }}
                    {% elseif quoteText %}
                        <p></p>
                        <div>
                            <div class="quote-header"><a href="/user/{{ quoteUserId }}/">{{ quoteUser }}</a> on{{ " " }}{{ quoteDate }} said:</div>
                            <blockquote>
                                {{ quoteText }}
                            </blockquote>
                        </div>
                        <p></p>
                    {% endif %}
                {% endautoescape %}
            </textarea>
        </div>
        <div class="form-block">
            {% if canLockOrSticky %}
                <span class="radio-button-group"><input type="checkbox" name="sticky" value="sticky"{% if POST.sticky %}{{ " " }}checked{% elseif post.Sticky %}{{ " " }}checked{% endif %}/>Sticky Thread</span><br />
                <span class="radio-button-group"><input type="checkbox" name="locked" value="locked"{% if POST.locked %}{{ " " }}checked{% elseif post.Locked %}{{ " " }}checked{% endif %}/>Lock Thread</span><br />
                <span class="radio-button-group"><input type="checkbox" name="news" value="news"{% if POST.news %}{{ " " }}checked{% elseif post.NewsPost %}{{ " " }}checked{% endif %}/>News Post</span><br />
            {% endif %}
            {% if canMoveThread %}
                <select name="move-board">
                    {% for board in allBoards %}
                        <option value="{{ board.BoardId }}"{% if board.BoardId == post.ParentId %}{{ " " }}selected{% endif %}>{% if board.depth > 0 %}{% for depth in 1..board.depth %}&nbsp;&nbsp;{% endfor %}{% endif %}{{ board.Name }}</option>
                    {% endfor %}
                </select>
            {% endif %}
        </div>
        <input type="submit" name="submit" value="Save" />
    </form>
    <ul class="comment-list">
        {% for comment in thread.posts|reverse %}
            {{ block('comment') }}
        {% endfor %}
    </ul>
{% endblock %}
