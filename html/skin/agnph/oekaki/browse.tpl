{% extends "oekaki/skin-base.tpl" %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
    {% endif %}
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <script src="{{ asset('/scripts/tinymce-spoiler-plugin.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".oekaki-comment-list-container").each(function(i, container) {
                container = $(container);
                var elem = container.find(".toggleable-comment-list");
                var button = container.find(".toggle-comments-button-container input[type=button]");
                elem.addClass("hidden-comment-list");
                button.click(toggleComments);
                function toggleComments() {
                    if (elem.hasClass("hidden-comment-list")) {
                        elem.removeClass("hidden-comment-list");
                        elem.addClass("shown-comment-list");
                        button.val("Hide Comments");
                    } else {
                        elem.removeClass("shown-comment-list");
                        elem.addClass("hidden-comment-list");
                        button.val("Show Comments");
                    }
                }
            });

            $("form.oekaki-make-comment-container").each(function(i, container) {
                container = $(container);
                var button = container.find(".oekaki-make-comment-button");
                var pane = container.find(".oekaki-make-comment-editor-container");
                pane.hide();
                button.click(function() {
                    if (!pane.is(":visible")) {
                        pane.show();
                        tinymce.init({
                            selector: "#" + container.attr("id") + " textarea.oekaki-make-comment-textbox",
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
                    } else {
                        {# Post comment #}
                        container[0].submit();
                    }
                });
            });
        });
    </script>
{% endblock %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/comments-style.css') }}" />
{% endblock %}

{% use 'includes/comment-block.tpl' %}

{% block oekaki_post %}
    <div class="oekaki-post">
        <div class="oekaki-post-header">
            <span class="oekaki-post-header-item"><label>Artist:</label>&nbsp;<a href="/user/{{ post.user.UserId }}/oekaki/">{{ post.user.DisplayName }}</a></span>
                <span class="oekaki-post-header-item-separator">|</span>
            <span class="oekaki-post-header-item"><label>Title:</label>&nbsp;{% autoescape false %}{{ post.escapedTitle }}{% endautoescape %}</span>
                <span class="oekaki-post-header-item-separator">|</span>
            <span class="oekaki-post-header-item"><label>Date:</label>&nbsp;{{ post.date }}</span>
                {% if post.duration %}<span class="oekaki-post-header-item-separator">|</span>
            <span class="oekaki-post-header-item"><label>Time:</label>&nbsp;{{ post.duration }}</span>{% endif %}
        </div>
        <div class="Clear">&nbsp;</div>
        <div class="oekaki-post-content">
            <div class="oekaki-post-image-container">
                {% if post.Status == 'A' %}
                    <a href="/oekaki/image/{{ post.PostId }}.{{ post.Extension }}">
                        <noscript>
                            <img class="oekaki-post-image" src="/oekaki/image/{{ post.PostId }}.{{ post.Extension }}" />
                        </noscript>
                        <img class="oekaki-post-image jsonly" data-src="/oekaki/image/{{ post.PostId }}.{{ post.Extension }}" hidden />
                    </a>
                    {% if post.HasAnimation %}<p><a href="/oekaki/animation/{{ post.PostId }}/">(Show Animation)</a><p>{% endif %}
                {% elseif post.Status == 'M' %}
                    <img class="oekaki-post-image" src="/images/deleted-preview.png" />
                {% elseif post.Status == 'D' %}
                    <img class="oekaki-post-image" src="/images/deleted-preview.png" />
                {% else %}
                    <img class="oekaki-post-image" src="/images/deleted-preview.png" />
                {% endif %}
            </div>
            <div class="oekaki-comment-list-container">
                <div class="toggle-comments-button-container">
                    <input type="button" value="Show Comments" />
                </div>
                <div class="toggleable-comment-list">
                    <ul class="oekaki-comment-list">
                        {% set comment = post %}
                        {{ block('comment') }}
                        {% for comment in post.comments %}
                            {{ block('comment') }}
                        {% endfor %}
                    </ul>
                </div>
                <form id="p{{ post.PostId }}" class="oekaki-make-comment-container" action="/oekaki/comment/" method="POST" accept-encoding="UTF-8">
                    <input type="hidden" name="post-id" value="{{ post.PostId }}" />
                    <input type="hidden" name="action" value="comment" />
                    <div class="oekaki-make-comment-editor-container">
                        <textarea name="text" class="oekaki-make-comment-textbox">
                        </textarea>
                    </div>
                    <input type="button" class="oekaki-make-comment-button" value="Comment" />
                </form>
            </div>
        </div>
    </div>
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div class="iterator">
        {% autoescape false %}{{ iterator }}{% endautoescape %}
    </div>
    <div class="Clear">&nbsp</div>
    <ul class="oekaki-post-list">
        {% for post in posts %}
            <li>
                {{ block('oekaki_post') }}
            </li>
        {% endfor %}
    </ul>
    <div class="Clear">&nbsp</div>
    <div class="iterator">
        {% autoescape false %}{{ iterator }}{% endautoescape %}
    </div>
{% endblock %}
