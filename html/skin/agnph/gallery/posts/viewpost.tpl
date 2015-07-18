{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/viewpost-style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/comments-style.css" />
{% endblock %}

{% block scripts %}
    {% if post.canEdit %}
        <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
        <script src="{{ skinDir }}/gallery/posts/viewpost-script.php?pi={{ post.PostId }}&ppi={{ post.ParentPoolId }}{% if user and user.NavigateGalleryPoolsWithKeyboard %}&keynav=1{% endif %}"></script>
    {% endif %}
{% endblock %}

{% use 'includes/comment-block.tpl' %}

{% block parent_child_block %}
    {% if post.ParentPostId != -1 %}
        <div class="parentbox">
            <p><strong>Parent Post</strong></p>
            <p><a href="/gallery/post/show/{{ post.ParentPostId }}/"># {{ post.ParentPostId }}</a></p>
        </div>
    {% endif %}
    {% if post.hasChildren %}
        <div class="parentbox">
            <p><strong>Child Posts</strong></p>
            <p><a href="/gallery/post/?search=parent%3A{{ post.PostId }}">here</a></p>
        </div>
    {% endif %}
{% endblock %}

{% block pool_iterator_block %}
    {% if post.poolIterator %}
        <div class="poolbox">
            <p><strong>Pool</strong></p>
            {% autoescape false %}
            <p>{{ post.poolIterator }}</p>
            {% endautoescape %}
        </div>
    {% endif %}
{% endblock %}

{% block search_block %}
    <div class="searchbox">
        <h3>Search</h3>
        <form action="/gallery/post/" accept-charset="UTF-8">
            <input class="search" name="search" type="text" required />
        </form>
    </div>
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div class="post-layout-container">
        <div class="sidepanel">
            {{ block('parent_child_block') }}
            <div class="side-search-pool">
                {{ block('search_block') }}
                <hr />
                {{ block('pool_iterator_block') }}
            </div>
            <ul class="sidepanel-list">
                <li>
                    <h3>Tags:</h3>
                    <div class="tagbox">
                        <ul class="taglist">
                            {% for category in post.tagCategories %}
                                <li class="tagcategory">
                                    <strong>{{ category.name }}</strong>
                                    <ul class="taglist">
                                        {% for tag in category.tags %}
                                            <li class="tag">
                                                <a href="/gallery/post/?search={{ tag.Name|url_encode }}" class="{{ tag.Type|lower }}typetag">{{ tag.displayName }}</a>
                                            </li>
                                        {% endfor %}
                                    </ul>
                                </li>
                            {% endfor %}
                        </ul>
                    </div>
                </li>
                <li>
                    <h3>Statistics</h3>
                    <div class="statbox">
                        <ul class="statlist">
                            <li>ID: {{ post.PostId }}</li>
                            {% if post.Source != "" %}
                                <li>Source:
                                    {% if post.Source starts with "http://" or post.Source starts with "https://" %}
                                        <a href="{{ post.Source }}">{{ post.Source }}</a>
                                    {% else %}
                                        {{ post.Source }}
                                    {% endif %}
                                </li>
                            {% endif %}
                            <li>Posted: {% autoescape false %}{{ post.postedHtml }}{% endautoescape %}</li>
                            <li>Rating: {% autoescape false %}{{ post.ratingHtml }}{% endautoescape %}</li>
                            <li>Favorites: {{ post.NumFavorites }}</li>
                            {% if post.FileSize != "" %}<li>Size: {{ post.FileSize }}</li>{% endif %}
                            <li>Views: {{ post.NumViews }}</li>
                            <li><a href="/gallery/post/show/{{ post.PostId }}/history/">Tag History</a></li>
                        </ul>
                    </div>
                </li>
                {% if post.canEdit %}
                <li>
                    <h3>Actions</h3>
                    <div class="actionbox">
                        <ul class="actionlist">
                            <li><a href="/gallery/post/show/{{ post.PostId }}/" onclick="return toggleEdit();">Edit</a></li>
                            {% if post.canApprove %}
                                <li>
                                    <form hidden id="approveform" method="POST" accept-charset="UTF-8">
                                        <input name="post" type="hidden" value="{{ post.PostId }}" />
                                        <input name="action" type="hidden" value="approve" />
                                    </form>
                                    <a href="#" onclick="$('#approveform')[0].submit();return false;">Approve Post</a>
                                </li>
                            {% endif %}
                            {% if post.canFlag %}
                                <li>
                                    <a id="flagaction" href="#">{# No text for no javascript #}</a>
                                    <div class="flageditbox">
                                        <form method="POST" accept-charset="UTF-8">
                                            <label>Reason:</label><br />
                                            <input name="post" type="hidden" value="{{ post.PostId }}" />
                                            <input id="flag-edit-text" name="reason" type="text" />
                                            <input name="action" type="hidden" value="flag" />
                                            <input type="submit" value="Flag" />
                                        </form>
                                    </div>
                                </li>
                            {% endif %}
                            {% if post.canUnflag %}
                                <li>
                                    <form hidden id="unflagform" method="POST" accept-charset="UTF-8">
                                        <input name="post" type="hidden" value="{{ post.PostId }}" />
                                        <input name="action" type="hidden" value="unflag" />
                                    </form>
                                    <a href="#" onclick="$('#unflagform')[0].submit();return false;">Unflag Post</a>
                                </li>
                            {% endif %}
                            {% if post.canDelete %}
                                <li>
                                    <form hidden id="deleteform" method="POST" accept-charset="UTF-8">
                                        <input name="post" type="hidden" value="{{ post.PostId }}" />
                                        <input name="reason" type="hidden" value="" />
                                        <input name="action" type="hidden" value="delete" />
                                    </form>
                                    <a href="#" onclick="$('#deleteform')[0].submit();return false;">Delete Post</a>
                                </li>
                            {% endif %}
                            {% if post.canUnDelete %}
                                <li>
                                    <form hidden id="undeleteform" method="POST" accept-charset="UTF-8">
                                        <input name="post" type="hidden" value="{{ post.PostId }}" />
                                        <input name="action" type="hidden" value="undelete" />
                                    </form>
                                    <a href="#" onclick="$('#undeleteform')[0].submit();return false;">Undelete Post</a>
                                </li>
                            {% endif %}
                            <li>
                                <a id="poolaction" href="#"></a><span id="poolactionworking" hidden>
                                <small>Processing...</small></span>
                                <div class="pooleditbox">
                                    <label>Search for Pool:</label><br />
                                    <input id="pooleditfield" type="text" />
                                    <ul id="poolautocomplete">
                                    </ul>
                                </div>
                            </li>
                            <li>
                                {% if isFavorited %}
                                    <form id="favorite-form" method="POST">
                                        <input type="hidden" name="action" value="remove-favorite" />
                                        <a href="#" onclick="document.getElementById('favorite-form').submit();return false;">Remove from Favorites</a>
                                    </form>
                                {% else %}
                                    <form id="favorite-form" method="POST">
                                        <input type="hidden" name="action" value="add-favorite" />
                                        <a href="#" onclick="document.getElementById('favorite-form').submit();return false;">Add to Favorites</a>
                                    </form>
                                {% endif %}
                            </li>
                            {% if post.canSetAvatar %}
                                <li>
                                    <form id="set-avatar-form" method="POST">
                                        <input type="hidden" name="action" value="set-avatar" />
                                        <a href="#" onclick="document.getElementById('set-avatar-form').submit();return false;">Set as Avatar</a>
                                    </form>
                                </li>
                            {% endif %}
                        </ul>
                    </div>      {# Actions div box #}
                </li>
                {% endif %} {# post.canEdit #}
            </ul>
        </div>
        <div class="mainpanel">
            <div class="top-search-pool">
                {{ block('search_block') }}
                {{ block('pool_iterator_block') }}
                <hr />
            </div>
            {% if post.Status!="D" %}
                {# Only render image if status is not deleted #}
                <p>
                    {% if post.Extension == "swf" %}
                        {# Some messy scaling calculations :( #}
                        <div style="position:relative;padding-bottom:{{ post.Height * 100 / post.Width }}%;padding-top:0px;height:0;overflow:hidden;">
                            <object style="position:absolute;top:0;left:0;width:100%;height:100%;" data="{{ post.downloadUrl }}"></object>
                        </div>
                    {% elseif post.Extension == "webm" %}
                        <video class="previewImg" preload controls loop=true>
                            <source src="{{ post.downloadUrl }}" type="video/webm" />
                            Your browser does not support the video tag.
                        </video>
                    {% else %}
                        {% if post.previewUrl==post.downloadUrl %}
                            <img class="previewImg" src="{{ post.previewUrl }}" />
                        {% else %}
                            <a href="{{ post.downloadUrl }}"><img class="previewImg" src="{{ post.previewUrl }}" /></a>
                        {% endif %}
                    {% endif %}
                </p>
                {#<p>
                    {{ post.Description }}
                </p>#}
                <p>
                    {% if user.UserId > 0 %}<a href="/gallery/post/show/{{ post.PostId }}/" onclick="return toggleEdit();">Edit</a> | {% endif %}<a href="{{ post.downloadUrl }}">Download</a>
                </p>
                <div class="posteditbox">
                    <a id="editanchor" />
                    <form method="POST" accept-charset="UTF-8">
                        <input type="hidden" name="action" value="edit" />
                        <label class="formlabel">Rating</label>         <input name="rating" type="radio"{% if post.Rating=='e' %} checked{% endif %} value="e" /><label>Explicit</label>
                                                                        <input name="rating" type="radio"{% if post.Rating=='q' %} checked{% endif %} value="q" /><label>Questionable</label>
                                                                        <input name="rating" type="radio"{% if post.Rating=='s' %} checked{% endif %} value="s" /><label>Safe</label><br />
                        <label class="formlabel">Parent</label>         <input id="parent" class="textbox" type="textbox" name="parent" value="{% if post.ParentPostId!=-1 %}{{ post.ParentPostId }}{% endif %}" /><br />
                        <label class="formlabel">Source</label>         <input id="imgsource" class="textbox" type="textbox" size=35 name="source" value="{{ post.Source }}" /><br />
                        <label class="formlabel">Tags</label>           <textarea id="tags" class="textbox" name="tags">{{ post.tagstring }}</textarea><br />
                        {# TODO: Add description support #}
                        {# <label class="formlabel">Description</label>    <textarea id="desc" class="textbox" name="description">{{ post.Description }}</textarea><br /> #}<input type="hidden" name="description" value="" />
                        <br />
                        <input type="submit" value="Save Changes" />
                    </form>
                </div>
                <div class="Clear">&nbsp;</div>
            {% endif %}
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
                    <span class="comment-iterator">{% autoescape false %}{{ commentIterator }}{% endautoescape %}</span>
                {% endif %}
            </div>
        </div>
    </div>
{% endblock %}
