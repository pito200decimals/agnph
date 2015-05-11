{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/postindex-style.css" />
{% endblock %}

{% block scripts %}
    {% if cansort %}
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
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

{% block gallerycontent %}
    <div class="sidepanel">
        <div class="searchbox">
            <h3>Search</h3>
            <form action="/gallery/post/" accept-charset="UTF-8">
                <input class="search" name="search" value="{{ search }}" type="textfield" required />
            </form>
        </div>
    </div>
    <div class="mainpanel">
        {% if posts|length > 0 %}
            {# Display search index. #}
            <ul class="sortable list">
                {% for post in posts %}
                    <li class="dragitem">
                        <input class="postid" type="hidden" value="{{ post.PostId }}" />
                        <input class="postorder" type="hidden" value="{{ post.PoolItemOrder }}" />
                        <a class="postlink" href="/gallery/post/show/{{ post.PostId }}/">
                            <div class="postsquare">
                                <div class="postsquarepreview">
                                    {# TODO: Deleted thumbnail instead of preview #}
                                    <img class="postsquarepreview {{ post.outlineClass }}" src="{{ post.thumbnail }}" />
                                </div>
                                <div class="postlabel">
                                    {% autoescape false %}
                                    {{ post.scoreHtml }}{{ post.favHtml }}{{ post.commentsHtml }}{{ post.ratingHtml }}
                                    {% endautoescape %}
                                </div>
                            </div>
                        </a>
                    </li>
                {% endfor %}
            </ul>
            <div class="Clear">&nbsp;</div>
            <div class="indexIterator">
                {% autoescape false %}
                {{ postIterator }}
                {% endautoescape %}
            </div>
        {% else %}
            {# No posts here. #}
            <p>
                No posts matched your search.
            <p>
        {% endif %}
    </div>
{% endblock %}
