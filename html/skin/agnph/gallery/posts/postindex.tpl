{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/postindex-style.css') }}" />
    {% if canMassTagEdit %}
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
    {% if canMassTagEdit %}
        <script>
            $(document).ready(function() {
                $("#mass-tag-edit-toggle").click(function() {
                    $("#mass-tag-edit").toggle();
                });
            });
        </script>
    {% endif %}
{% endblock %}

{% block content %}
    {{ block('banner') }}
    <div class="sidepanel">
        <div class="searchbox">
            <h3>Search</h3>
            <form accept-charset="UTF-8">
                <input class="search" name="search" value="{{ search }}" type="text" required />
            </form>
        </div>
        {# TODO: Related tags go here #}
    </div>
    <div class="mainpanel">
        {% if posts|length > 0 %}
            {# Display search index. #}
            <ul class="sortable list post-list">
                {% for post in posts %}
                    <li class="dragitem">
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
    {% if canMassTagEdit %}
        <div class="footer-panel desktop-only">
            <div id="mass-tag-edit-toggle">
                <a>Mass tag edit</a>
            </div>
            <div id="mass-tag-edit">
                <hr />
                <form method="POST" accept-charset="UTF-8">
                    <p>
                        This will apply these tag modifications to all posts in the current search.
                    </p>
                    <table>
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
                            <td><input type="submit" name="submit" value="Apply Changes" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    {% endif %}
{% endblock %}
