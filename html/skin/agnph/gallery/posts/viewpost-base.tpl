{% extends 'gallery/base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/gallery/viewpost-style.css')|raw }}
    {{ inline_css_asset('/comments-style.css')|raw }}
    {{ inline_css_asset('/tag-complete-style.css')|raw }}
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script>
        var pi = {{ post.PostId }};
        var ppi = {{post.ParentPoolId }};
    </script>
    <script src="{{ asset('/scripts/tinymce.min.js') }}"></script>
    <script src="{{ asset('/scripts/tinymce-spoiler-plugin.js') }}"></script>
    <script>
        var COMMENTS_STYLE_CSS = "{{ asset('/comments-style.css') }}";
    </script>
    <script src="{{ asset('/scripts/gallery.js') }}"></script>
    {% if post.canEdit %}
        <script src="{{ asset('/scripts/jquery.autocomplete.min.js') }}"></script>
        <script src="{{ asset('/scripts/gallery-edit.js') }}"></script>
        <script src="{{ asset('/scripts/tag-complete.js') }}"></script>
        <script>
            var AddTag;
            var OnEditSubmit;
            (function() {
                var tag_search_url = '/gallery/tagsearch/';
                function GetPreclass(pre) {
                    var preclass = null;
                    if (pre.toLowerCase() == 'artist') {
                        preclass = 'atypetag';
                    }
                    if (pre.toLowerCase() == 'copyright') {
                        preclass = 'btypetag';
                    }
                    if (pre.toLowerCase() == 'character') {
                        preclass = 'ctypetag';
                    }
                    if (pre.toLowerCase() == 'species') {
                        preclass = 'dtypetag';
                    }
                    if (pre.toLowerCase() == 'general') {
                        preclass = 'mtypetag';
                    }
                    return preclass;
                }
                var fns = SetUpTagCompleter(tag_search_url, GetPreclass, ".g");
                AddTag = fns.AddTag;
                OnEditSubmit = fns.OnEditSubmit;
            })();
        </script>
    {% endif %}
    {% if user and user.NavigateGalleryPoolsWithKeyboard %}
        <script src="{{ asset('/scripts/gallery-keyboard.js') }}"></script>
    {% endif %}

    {# Script for adding tags in fancy-tagger UI #}
    {% if post.Status!="D" %}
        {% if post.canEdit %}
            {% if not user.PlainGalleryTagging %}
                <script>
                    $(document).ready(function() {
                        {% for category in post.tagCategories %}
                            {% for tag in category.tags %}
                                AddTag('{{ tag.Name }}', '{{ tag.Type|lower }}');
                            {% endfor %}
                        {% endfor %}
                    });
                </script>
            {% endif %}
        {% endif %}
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
    <div id="gallery-search">
        <h3>Search<a id="search-help-link" href="/gallery/help/" title="Search Help">?</a></h3>
        <form action="/gallery/post/" accept-charset="UTF-8">
            <div class="search">
                <input class="search" name="search" value="{{ search }}" type="text" required placeholder="Search" />
                <input type="submit" class="search-button" value="" />
            </div>
        </form>
    </div>
{% endblock %}

{% block sidepanel_block %}
    {% if tag_toggle_prefix is not defined %}
        {% set tag_toggle_prefix = "" %}
    {% endif %}
    <ul class="sidepanel-list">
        <li>
            <h3>Tags:</h3>
            <ul class="taglist">
                {% if post.tagCategories|length > 0 %}
                    {% for category in post.tagCategories %}
                        <li class="tagcategory">
                            <input type="checkbox" class="tag-category-toggle" id="{{ tag_toggle_prefix }}{{ category.name }}-toggle" hidden checked />
                            <label for="{{ tag_toggle_prefix }}{{ category.name }}-toggle" class="tag-category-toggle-button"><strong>{{ category.name }}</strong></label>
                            <ul class="taglist">
                                {% for tag in category.tags %}
                                    <li class="tag">
                                        <a href="/gallery/post/?search={{ tag.quotedName|url_encode }}" class="{{ tag.Type|lower }}typetag">{{ tag.displayName }}</a> <small>{{ tag.ItemCount }}</small>
                                    </li>
                                {% endfor %}
                            </ul>
                        </li>
                    {% endfor %}
                {% else %}
                    <li class="tag">
                        <span class="none-tag">None</span>
                    </li>
                {% endif %}
            </ul>
        </li>
        <li>
            <h3>Statistics</h3>
            <ul class="statlist">
                <li>ID: {{ post.PostId }}</li>
                {% if post.Source != "" %}
                    <li class="source-line">Source:
                        {% if post.Source starts with "http://" or post.Source starts with "https://" %}
                            <a href="{{ post.Source }}" title="{{ post.Source }}">{{ post.Source }}</a>
                        {% else %}
                            {{ post.Source }}
                        {% endif %}
                    </li>
                {% endif %}
                <li>Posted: {% autoescape false %}{{ post.postedHtml }}{% endautoescape %}</li>
                <li>Rating: {% autoescape false %}{{ post.ratingHtml }}{% endautoescape %}</li>
                <li>Favorites: {{ post.NumFavorites }}</li>
                <li>Size: {{ post.Width }} x {{ post.Height }}{% if post.FileSize != "" %} ({{ post.FileSize }}){% endif %}</li>
                <li>Views: {{ post.NumViews }}</li>
                <li><a href="/gallery/post/show/{{ post.PostId }}/history/">Tag History</a></li>
            </ul>
        </li>
        {% if post.hasAction %}
            <li>
                <h3>Actions</h3>
                <ul class="actionlist">
                    {% if post.canEdit %}
                        <li><a href="/gallery/post/show/{{ post.PostId }}/" onclick="return toggleEdit();">Edit</a></li>
                    {% endif %}
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
                                    <label for="flag-reason-select">Reason:</label><br />
                                    <input name="post" type="hidden" value="{{ post.PostId }}" />
                                    <select id="flag-reason-select" name="reason-select">
                                        {% for reason in flag_reasons %}
                                            <option value="{{ reason }}">{{ reason }}</option>
                                        {% endfor %}
                                    </select>
                                    <input id="extra-reason-text" name="extra-reason-text" type="search" placeholder="Post #" />
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
                    {% if post.canGenerateThumbnail %}
                        <li>
                            <form hidden id="gen-thumb-form" method="POST" accept-charset="UTF-8">
                                <input name="post" type="hidden" value="{{ post.PostId }}" />
                                <input name="action" type="hidden" value="regen-thumbnail" />
                            </form>
                            <a href="#" onclick="$('#gen-thumb-form')[0].submit();return false;">Regenerate Thumbnail</a>
                        </li>
                    {% endif %}
                    {% if post.canModifyPool %}
                        <li>
                            <a id="poolaction" href="#"></a>
                            <span id="poolactionworking" hidden><img src="/images/spinner.gif" /></span>
                            <div id="pooleditbox" class="search">
                                <input id="pool-edit-field" type="text" class="search" required placeholder="Pool name" />
                            </div>
                        </li>
                    {% endif %}
                    {% if post.canFavorite or post.canSetAvatar %}
                        <li>&nbsp;</li>
                    {% endif %}
                    {% if post.canFavorite %}
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
                    {% endif %}
                    {% if post.canSetAvatar %}
                        <li>
                            <form id="set-avatar-form" method="POST">
                                <input type="hidden" name="action" value="set-avatar" />
                                <a href="#" onclick="document.getElementById('set-avatar-form').submit();return false;">Set as Avatar</a>
                            </form>
                        </li>
                    {% endif %}
                </ul>
            </li>
        {% endif %}  {# post.hasAction #}
        <li>
            <h3>Related Posts</h3>
            <ul class="navlist">
                {% if prevPostId %}<li><a href="/gallery/post/show/{{ prevPostId }}/">Previous</a></li>{% endif %}
                {% if nextPostId %}<li><a href="/gallery/post/show/{{ nextPostId }}/">Next</a></li>{% endif %}
                <li><a href="/gallery/post/random/">Random</a></li>
                <li>&nbsp;</li>
            </ul>
            {% if post.Status!="D" %}
                <ul class="reverse-image-search-list">
                    <li><a href="{{ post.googleUrl }}">Reverse Google Search</a></li>
                    <li><a href="{{ post.saucenaoUrl }}">Reverse SauceNao Search</a></li>
                    <li><a href="{{ post.iqdbUrl }}">Reverse IQDB Search</a></li>
                    <li><a href="{{ post.harryluUrl }}">Reverse harry.lu Search</a></li>
                </ul>
            {% endif %}
        </li>
    </ul>
{% endblock %}

{% block main_image_block %}
    {% if post.Status!="D" %}
        {# Only render image if status is not deleted #}
        <div>
            {% if post.Extension == "swf" %}
                {# Some messy scaling calculations :( #}
                <div style="position:relative;padding-bottom:{{ post.Height * 100 / post.Width }}%;padding-top:0px;height:0;overflow:hidden;">
                    <object style="position:absolute;top:0;left:0;width:100%;height:100%;" data="{{ post.downloadUrl }}"></object>
                </div>
            {% elseif post.Extension == "webm" %}
                <video class="preview-img" preload controls loop=true>
                    <source src="{{ post.downloadUrl }}" type="video/webm" />
                    Your browser does not support the video tag.
                </video>
            {% else %}
                {% if post.previewUrl==post.downloadUrl %}
                    <img class="preview-img" src="{{ post.previewUrl }}" />
                {% else %}
                    <a href="{{ post.downloadUrl }}"><img class="preview-img" src="{{ post.previewUrl }}" /></a>
                {% endif %}
            {% endif %}
        </div>
        {% if post.Description|length > 0 %}
            <div class="post-description">
                <h4>Description</h4>
                {% autoescape false %}{{ post.Description }}{% endautoescape %}
            </div>
        {% endif %}
        <p>
            {% if user.UserId > 0 %}<a href="/gallery/post/show/{{ post.PostId }}/" onclick="return toggleEdit();">Edit</a> | {% endif %}<a id="download-link" href="{{ post.downloadUrl }}">Download</a>
        </p>
        {{ block('edit_panel_block') }}
        <div class="Clear">&nbsp;</div>
    {% endif %}
{% endblock %}

{% block edit_panel_block %}
    <div class="posteditbox">
        <a id="editanchor">&nbsp;</a>
        <form method="POST" accept-charset="UTF-8" onsubmit="OnEditSubmit()">
            <input type="hidden" name="action" value="edit" />
            <table>
            <tr>
                <td><label>Rating</label></td>
                <td>
                    <span class="radio-button-group"><input id="rating-e-box" name="rating" type="radio"{% if post.Rating=='e' %} checked{% endif %} value="e" /><label for="rating-e-box">Explicit</label></span>
                    <span class="radio-button-group"><input id="rating-q-box" name="rating" type="radio"{% if post.Rating=='q' %} checked{% endif %} value="q" /><label for="rating-q-box">Questionable</label></span>
                    <span class="radio-button-group"><input id="rating-s-box" name="rating" type="radio"{% if post.Rating=='s' %} checked{% endif %} value="s" /><label for="rating-s-box">Safe</label></span>
                </td>
            </tr>
            <tr>
                <td><label>Parent</label></td>
                <td><input id="parent" class="textbox" type="text" name="parent" value="{% if post.ParentPostId!=-1 %}{{ post.ParentPostId }}{% endif %}" /></td>
            </tr>
            <tr>
                <td><label>Source</label></td>
                <td><input id="imgsource" class="textbox" type="text" size=35 name="source" value="{{ post.Source }}" /></td>
            </tr>
            {% if not user.PlainGalleryTagging %}
                <tr>
                    <td><label>Tags</label></td>
                    <td><ul class="g autocomplete-tag-list"></ul><textarea class="g autocomplete-tags" name="tags" hidden>{{ post.tagstring }}</textarea></td>
                </tr>
                <tr>
                    <td><label>&nbsp;</label></td>
                    <td><input type="text" class="g textbox autocomplete-tag-input" placeholder="Enter Tag" /><span>&nbsp;</span></td>
                </tr>
            {% else %}
                <tr>
                    <td><label>Tags</label></td>
                    <td><textarea id="tags" class="textbox" name="tags">{{ post.tagstring }}</textarea></td>
                </tr>
            {% endif %}
            <tr>
                <td><label>Description</label></td>
                <td><textarea id="desc" class="textbox" name="description">{{ post.Description }}</textarea></td>
            </tr>
            <tr>
                <td><input type="submit" value="Save Changes" /></td>
                <td></td>
            </tr>
            </table>
        </form>
    </div>
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div class="post-layout-container">
        <div class="sidepanel">
            {{ block('parent_child_block') }}
            <div id="side-search-pool-container" class="desktop-only">
                {{ block('search_block') }}
                <hr />
                {{ block('pool_iterator_block') }}
            </div>
            {{ block('sidepanel_block') }}
        </div>
        <div class="mainpanel">
            <div id="top-search-pool-container" class="mobile-only">
                {# For mobile display #}
                {{ block('search_block') }}
                {{ block('pool_iterator_block') }}
                <hr />
            </div>
            {{ block('main_image_block') }}
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
