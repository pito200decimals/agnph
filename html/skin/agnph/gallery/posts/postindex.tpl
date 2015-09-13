{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/postindex-style.css" />
{% endblock %}

{% block scripts %}
    {{ parent() }}
    {% if not user or user.AutoDetectTimezone %}
        <script src="{{ skinDir }}/timezone.js"></script>
    {% endif %}
    {% if cansort %}
        <script src="{{ skinDir }}/scripts/jquery.sortable.js"></script>
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
{% endblock %}

{% block content %}
    <div class="sidepanel">
        <div class="searchbox">
            <h3>Search</h3>
            <form action="/gallery/post/" accept-charset="UTF-8">
                <input class="search" name="search" value="{{ search }}" type="text" required />
            </form>
        </div>
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
{% endblock %}
