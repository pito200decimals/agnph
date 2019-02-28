{% extends 'gallery/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/postindex-style.css') }}" />
    <link id="mobile-css" rel="stylesheet" type="text/css" href="{{ asset('/gallery/postindex-mobile.css') }}" {% if ignore_mobile %}disabled {% endif %}/>
    {% if canMassTagEdit or canMassDeletePosts %}
        <style>
            #mass-tag-edit-toggle {
                cursor: pointer;
            }
            #mass-tag-edit {
                display: none;
            }
            #mass-tag-edit td {
                vertical-align: top;
            }
            #mass-tag-edit textarea {
                min-height: 75px;
                width: 400px;
            }
        </style>
    {% endif %}
{% endblock %}

{% block scripts %}
    {{ parent() }}
    {# This is the homepage, so set timezone here #}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ asset('timezone.js') }}"></script>
    {% endif %}

    {% if user and user.NavigateGalleryPoolsWithKeyboard %}
        <script src="{{ asset('/scripts/gallery-keyboard.js') }}"></script>
    {% endif %}
    {% if cansort %}
        <script src="{{ asset('/scripts/jquery.sortable.js') }}"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $('.sortable').sortable().bind('sortupdate', Update);
            });
            function Update() {
                $('.sortable').sortable('destroy');
                $('.sortable').css("opacity", "0.5");
                var index = 1;
                var changed = [];
                $('.dragitem').each(function() {
                    var id = $('.postid', this)[0].value;
                    var oldindex = $('.postorder', this)[0].value;
                    var myindex = index++;
                    if (oldindex != myindex) {
                        changed.push({
                            postid: id,
                            oldindex: oldindex,
                            newindex: myindex
                        });
                    }
                });
                if (changed.length > 0) {
                    $.ajax("/gallery/pools/reorder/{{ poolId }}/", {
                        data: {
                            values: changed
                        },
                        method: "POST",
                        success: function(e) {
                            $(changed).each(function() {
                                var id = this.postid;
                                var newindex = this.newindex;
                                $('.dragitem').each(function() {
                                    if ($('.postid', this)[0].value == id) {
                                        $('.postorder', this)[0].value = newindex;
                                    }
                                });
                            });
                            $('.sortable').sortable();
                            $('.sortable').removeAttr("style");
                        },
                        error: function(e) {
                            $('.sortable').sortable('destroy');
                            location.reload();
                        }
                    });
                }
            }
        </script>
    {% endif %}
    {% if canMassTagEdit or canMassDeletePosts %}
        <script>
            $(document).ready(function() {
                $("#mass-tag-edit-toggle").click(function() {
                    $("#mass-edit-action").val("tagedit");
                    $("#mass-tag-edit").toggle();
                });
                $("#delete-all-button").click(function() {
                    if (confirm("Are you absolutely sure you want to delete all posts in this search?")) {
                        $("#mass-edit-action").val("delete");
                        $("#mass-tag-edit").hide();
                        $("#mass-edit-submit-button").click();
                    }
                });
            });
        </script>
    {% endif %}
    {# For mobile layout toggle #}
    <script>
        function ToggleMobile() {
            var disable = !$('#mobile-css')[0].disabled;
            $.ajax("/gallery/set-mobile/", {
                data: {
                    disabled: disable
                },
                method: "POST",
                xhrFields: {
                    withCredentials: true
                }
            });
            $('#mobile-css')[0].disabled = disable;
            return false;
        }
        {% if ignore_mobile %}
            $(document).ready(function() {
                $('#mobile-css')[0].disabled = true;
            });
        {% endif %}
    </script>
    <link rel="stylesheet" type="text/css" href="{{ asset('/scripts/slideshow/photoswipe.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/scripts/slideshow/default-skin.css') }}" />
    <script src="{{ asset('/scripts/slideshow/photoswipe.min.js') }}"></script>
    <script src="{{ asset('/scripts/slideshow/photoswipe-ui-default.min.js') }}"></script>
    <script>
        var searchString = "{% autoescape false %}{{ search|e('js') }}{% endautoescape %}";
        var startPage = {{ page }};
        var pagesize = {{ pagesize }};
    </script>
    <script src="{{ asset('/scripts/gallery-slideshow.js') }}"></script>
{% endblock %}

{% block sidepanel %}
    <div class="sidepanel">
        <div id="gallery-search">
            <h3>Search<a id="search-help-link" href="/gallery/help/" title="Search Help">?</a></h3>
            <form accept-charset="UTF-8">
                <div class="search">
                    <input class="search" name="search" value="{{ search }}" type="text" required placeholder="Search" />
                    <input type="submit" class="search-button" value="" />
                </div>
            </form>
            <a style="float: left; margin-top: 10px;" href="" onclick="return OpenSlideshow();">Slideshow</a>
            <a style="float: right; margin-top: 10px;" class="toggle-mobile-layout" href="" onclick="return ToggleMobile();">Toggle Mobile</a>
            <div class="Clear">&nbsp;</div>
        </div>
        {# TODO: Related tags go here #}
    </div>
{% endblock %}

{% block mainpanel %}
    <div class="mainpanel">
        {% if posts|length > 0 %}
            {# Display search index. #}
            <ul class="sortable list post-list">
                {% for post in posts %}
                    <li class="dragitem{% if post.outlineClass == 'featuredoutline' %} featuredtile{% endif %}">
                        {% if cansort %}
                            <input class="postid" type="hidden" value="{{ post.PostId }}" />
                            <input class="postorder" type="hidden" value="{{ post.PoolItemOrder }}" />
                        {% endif %}
                        <a class="postlink" href="/gallery/post/show/{{ post.PostId }}/">
                            <div class="post-tile">
                                {# TODO: Deleted thumbnail instead of preview? #}
                                <img class="post-preview-img {{ post.outlineClass }}" src="{{ post.thumbnail }}" />
                                <div class="post-label">
                                    {% autoescape false %}
                                    {{ post.favHtml }}{{ post.commentsHtml }}{{ post.ratingHtml }}
                                    {% endautoescape %}
                                </div>
                            </div>
                        </a>
                    </li>
                {% endfor %}
            </ul>
            <div class="Clear">&nbsp;</div>
            <div class="iterator">
                {% autoescape false %}{{ postIterator }}{% endautoescape %}
            </div>
        {% else %}
            {# No posts here. #}
            <p>
                No posts matched your search.
            <p>
        {% endif %}
    </div>
{% endblock %}

{% block content %}
    {{ block('banner') }}
    {% if similar_tags|length > 0 %}
        <div class="similar-tags">
            Maybe you meant:
            {% for tag in similar_tags %}
                <span class="tag">
                    <a href="/gallery/post/?search={{ tag.quotedName|url_encode }}" class="{{ tag.Type|lower }}typetag">{{ tag.displayName }}</a>{% if loop.index0 < similar_tags|length - 1 %},{% endif %}
                </span>
            {% endfor %}
        </div>
    {% endif %}
    {{ block('sidepanel') }}
    {{ block('mainpanel') }}
    {% if canMassTagEdit or canMassDeletePosts %}
        <div class="footer-panel desktop-only">
            <div id="mass-tag-edit-toggle">
                <a>Mass Edit</a>
            </div>
            <div id="mass-tag-edit">
                <hr />
                <form method="POST" accept-charset="UTF-8">
                    <input id="mass-edit-action" type="hidden" name="mass-edit-action" value="tagedit" />
                    <p>
                        This will apply these tag modifications to all posts in the current search.
                    </p>
                    <table>
                        {% if canMassTagEdit %}
                            <tr>
                                <td><label>Tags to add:</label></td>
                                <td><textarea name="tags-to-add"></textarea></td>
                            </tr>
                            <tr>
                                <td><label>Tags to remove:</label></td>
                                <td><textarea name="tags-to-remove"></textarea></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><input id="mass-edit-submit-button" type="submit" name="submit" value="Apply Tag Changes" /></td>
                            </tr>
                        {% endif %}
                        {% if canMassTagEdit and canMassDeletePosts %}
                            <tr>
                                <td colspan="2">&nbsp;</td>
                            </tr>
                        {% endif %}
                        {% if canMassDeletePosts %}
                            <tr>
                                <td>Delete All Posts</td>
                                <td>
                                    <select id="flag-reason-select" name="reason-select">
                                        {% for reason in flag_reasons %}
                                            <option value="{{ reason }}">{{ reason }}</option>
                                        {% endfor %}
                                    </select>
                                    <input id="delete-all-button" type="button" value="Delete All Posts" />
                                </td>
                            </tr>
                        {% endif %}
                    </table>
                </form>
            </div>
        </div>
    {% endif %}
    
    {# HTML for slideshow gallery #}
    {# Find more details at http://photoswipe.com/documentation/getting-started.html #}
    <div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="pswp__bg"></div>
        <div class="pswp__scroll-wrap">
            <div class="pswp__container">
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
            </div>
            <div class="pswp__ui pswp__ui--hidden">
                <div class="pswp__top-bar">
                    <div class="pswp__counter"></div>
                    <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                    <button class="pswp__button pswp__button--share" title="Share"></button>
                    <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                    <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                    <button class="pswp__button pswp__button--start--autoplay" style="float: right; position: relative;" title="Autoplay"></button>
                    <div class="pswp__preloader">
                        <div class="pswp__preloader__icn">
                          <div class="pswp__preloader__cut">
                            <div class="pswp__preloader__donut"></div>
                          </div>
                        </div>
                    </div>
                </div>
                <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                    <div class="pswp__share-tooltip"></div> 
                </div>
                <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
                </button>
                <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
                </button>
                <div class="pswp__caption">
                    <div class="pswp__caption__center"></div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
