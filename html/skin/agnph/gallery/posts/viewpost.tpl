{% extends 'gallery/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/viewpost-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/comments-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/tag-complete-style.css') }}" />
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script>
        var pi = {{ post.PostId }};
        var ppi = {{post.ParentPoolId }};
    </script>
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <script src="{{ asset('/scripts/tinymce-spoiler-plugin.js') }}"></script>
    <script>
        $(document).ready(function() {
            tinymce.init({
                selector: "textarea.commenttextbox",
                plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor", "spoiler" ],
                target_list: [ {title: 'New page', value: '_blank'} ],
                toolbar: "undo redo | bold italic underline | bullist numlist | image link | code blockquote spoiler",
                contextmenu: "image link | hr",
                autoresize_max_height: 150,
                resize: false,
                menubar: false,
                relative_urls: false,
                content_css: "{{ asset('/comments-style.css') }}"
            });
            $("#commentbutton").click(function() {
                $("#commentbutton").hide();
                $("#commentform").show();
                $("html body").animate(
                    { scrollTop: $("#commentform").offset().top },
                    { duration: 0,
                      complete: function() {
                        tinyMCE.get("commenttextbox").focus();
                    }});
            });
        });
    </script>
    {% if post.canEdit %}
        <script src="{{ asset('/scripts/jquery.autocomplete.min.js') }}"></script>
        <script src="{{ asset('/scripts/gallery-edit.js') }}"></script>
        <script>
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
        </script>
        <script src="{{ asset('/scripts/tag-complete.js') }}"></script>
    {% endif %}
    {% if user and user.NavigateGalleryPoolsWithKeyboard %}
        <script src="{{ asset('/scripts/gallery-keyboard.js') }}"></script>
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
        <h3>Search<a id="search-help-link" href="/gallery/help/" title="Search Help">?</a></h3>
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
                    <ul class="taglist">
                        {% if post.tagCategories|length > 0 %}
                            {% for category in post.tagCategories %}
                                <li class="tagcategory">
                                    <strong>{{ category.name }}</strong>
                                    <ul class="taglist">
                                        {% for tag in category.tags %}
                                            <li class="tag">
                                                <a href="/gallery/post/?search={{ tag.quotedName|url_encode }}" class="{{ tag.Type|lower }}typetag">{{ tag.displayName }}</a><small>{{ tag.ItemCount }}</small>
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
                                    <div class="pooleditbox">
                                        <input id="pool-edit-field" type="text" class="search" required />
                                    </div>
                                </li>
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
                    <ul class="reverse-image-search-list">
                        <li><a href="{{ post.googleUrl }}">Reverse Google Search</a></li>
                        <li><a href="{{ post.saucenaoUrl }}">Reverse SauceNao Search</a></li>
                        <li><a href="{{ post.iqdbUrl }}">Reverse IQDB Search</a></li>
                        <li><a href="{{ post.harryluUrl }}">Reverse harry.lu Search</a></li>
                    </ul>
                </li>
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
                        {{ post.Description }}
                    </div>
                {% endif %}
                <p>
                    {% if user.UserId > 0 %}<a href="/gallery/post/show/{{ post.PostId }}/" onclick="return toggleEdit();">Edit</a> | {% endif %}<a id="download-link" href="{{ post.downloadUrl }}">Download</a>
                </p>
                <div class="posteditbox">
                    <a id="editanchor">&nbsp;</a>
                    <form method="POST" accept-charset="UTF-8" onsubmit="OnEditSubmit()">
                        <input type="hidden" name="action" value="edit" />
                        <table>
                        <tr>
                            <td><label>Rating</label></td>
                            <td>
                                <span class="radio-button-group"><input name="rating" type="radio"{% if post.Rating=='e' %} checked{% endif %} value="e" /><label>Explicit</label></span>
                                <span class="radio-button-group"><input name="rating" type="radio"{% if post.Rating=='q' %} checked{% endif %} value="q" /><label>Questionable</label></span>
                                <span class="radio-button-group"><input name="rating" type="radio"{% if post.Rating=='s' %} checked{% endif %} value="s" /><label>Safe</label></span>
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
                            <script>
                                $(document).ready(function() {
                                    {% for category in post.tagCategories %}
                                        {% for tag in category.tags %}
                                            AddTag('{{ tag.Name }}', '{{ tag.Type|lower }}');
                                        {% endfor %}
                                    {% endfor %}
                                });
                            </script>
                            <tr>
                                <td><label>Tags</label></td>
                                <td><ul class="g autocomplete-tag-list"></ul><textarea class="autocomplete-tags" name="tags" hidden>{{ post.tagstring }}</textarea></td>
                            </tr>
                            <tr>
                                <td><label>&nbsp;</label></td>
                                <td><input type="text" class="textbox autocomplete-tag-input" /></td>
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
                    <div class="iterator">
                        {% autoescape false %}{{ commentIterator }}{% endautoescape %}
                    </div>
                {% endif %}
            </div>
        </div>
    </div>
{% endblock %}
