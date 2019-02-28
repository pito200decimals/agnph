{% extends 'gallery/posts/viewpost-base.tpl' %}

{% set show_account_box_desktop_left_panel=false %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/gallery/retro-viewpost-style.css')|raw }}
{% endblock %}

{% use 'includes/comment-block.tpl' %}

{% block extra_section_nav_items %}
    <li class="divider desktop-only"></li>
    <li class="desktop-only">
        <ul class="section-nav">
        {{ block('account_box_desktop_items') }}
        </ul>
    </li>
{% endblock %}

{% block left_panel %}
    <div class="desktop-only">
        {{ block('parent_child_block') }}
        {% set tag_toggle_prefix = "desktop-" %}
        {{ block('sidepanel_block') }}
    </div>
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div class="post-layout-container">
        <div class="mainpanel">
            {{ block('pool_iterator_block') }}
            {{ block('main_image_block') }}
            <div class="mobile-only">
                {{ block('parent_child_block') }}
                {% set tag_toggle_prefix = "mobile-" %}
                {{ block('sidepanel_block') }}
            </div>
        </div>
        <div class="Clear">&nbsp;</div>
        <hr />
        <div class="footer-panel">
            <div class="comment-section">
                {% if post.comments|length > 0 %}
                    <ul class="comment-list">
                        {% for comment in post.comments %}
                            {{ block('comment') }}
                        {% endfor %}
                    </ul>
                {% else %}
                    <span class="no-comments">No comments posted</span>
                {% endif %}
                {% if user and post.canComment%}
                    <div class="comment-section-input">
                        <input id="commentbutton" type="button" value="Add Comment"/>
                        <form id="commentform" method="POST">
                            <input type="hidden" name="action" value="add-comment" />
                            <textarea id="commenttextbox" name="text" class="commenttextbox">
                            </textarea>
                            <input type="submit" value="Add Comment" />
                        </form>
                    </div>
                {% endif %}
                {% if post.comments|length > 0 %}
                    <div class="iterator">
                        {% autoescape false %}{{ commentIterator }}{% endautoescape %}
                    </div>
                {% endif %}
            </div>
        </div>
    </div>
{% endblock %}
