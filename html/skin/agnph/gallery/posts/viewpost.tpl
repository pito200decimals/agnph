{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/viewpost-style.css" />
{% endblock %}

{% block scripts %}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script type="text/javascript">
        var in_flight = null;
        function toggleEdit() {
            $(".posteditbox").toggle()[0].scrollIntoView();
            return false;
        }
        $(document).ready(function() {
            $("#tags").keydown(function(e) {
                if (e.keyCode == 13) {
                    $(this.form).submit();
                    return false;
                }
            });
            $("#pooleditfield").keypress(function() {
                PopulatePoolList(this.value);
            });
            {% if post.ParentPoolId==-1 %}
                SetupAdd();
            {% else %}
                SetupRemove({{ post.ParentPoolId }});
            {% endif %}
        });
        function SetupAdd() {
            $("#poolaction").off("click").click(function() {
                $(".pooleditbox").toggle();
                return false;
            }).text("Add to pool");
        }
        function SetupRemove(poolid) {
            $("#poolaction").off("click").click(function() {
                RemoveFromPool(poolid);
                return false;
            }).text("Remove from pool");
        }
        function PopulatePoolList(prefix) {
            if (in_flight != null) {
                in_flight.abort();
                in_flight = null;
            }
            $("#poolactionworking").show();
            in_flight = $.ajax("/gallery/pools/list/?prefix="+encodeURIComponent(prefix), {
                success: function(pools) {
                    var elements = $();
                    for (i = 0; i < pools.length; i++) {
                        (function(pool) {
                            elem = $('<li><a href="">Add to <span>'+pool.name+'</span></a></li>').data("id", pool.id);
                            elem.find("a").click(function(e) {
                                e.preventDefault();
                                AddToPool(pool.id);
                                return false;
                            });
                            elements = elements.add(elem);
                        })(pools[i]);
                    }
                    $("#poolautocomplete").empty().append(elements);
                    $("#poolactionworking").hide();
                },
                error: function(e) {
                    $("#poolactionworking").hide();
                }
            });
        }
        function AddToPool(poolid) {
            if (in_flight != null) {
                in_flight.abort();
                in_flight = null;
            }
            $("#poolactionworking").show();
            in_flight = $.ajax("/gallery/pools/modify/{{ post.PostId }}/"+poolid+"/", {
                data: {
                    action: "add"
                },
                method: "POST",
                success: function(e) {
                    SetupRemove(poolid);
                    $(".pooleditbox").hide();
                    $("#poolactionworking").hide();
                },
                error: function(x,s,t) {
                    SetupAdd();
                    $(".pooleditbox").hide();
                    $("#poolactionworking").hide();
                }
            });
        }
        function RemoveFromPool(poolid) {
            if (in_flight != null) {
                in_flight.abort();
                in_flight = null;
            }
            $("#poolactionworking").show();
            in_flight = $.ajax("/gallery/pools/addremove_pool.php?post={{ post.PostId }}&pool="+poolid, {
                data: {
                    action: "remove"
                },
                method: "POST",
                success: function(e) {
                    SetupAdd();
                    $(".pooleditbox").hide();
                    $("#poolactionworking").hide();
                },
                error: function(x,s,t) {
                    SetupRemove(poolid);
                    $(".pooleditbox").hide();
                    $("#poolactionworking").hide();
                }
            });
        }
    </script>
{% endblock %}

{% block gallerycontent %}
    <div class="headerbar">
        {% if post.Status=="P" %}
            <div class="pendingbox">
                <p><strong>This post is pending moderator approval</strong></p>
            </div>
        {% elseif post.Status == "F" %}
            <div class="flaggedbox">
                <p><strong>This post has been flagged for deletion</strong></p>
            </div>
        {% endif %}
    </div>
    <div class="sidepanel">
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
        <div class="searchbox">
            <h3>Search</h3>
            <form action="/gallery/post/">
                <input name="search" type="textfield" />
            </form>
        </div>
        <hr />
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
                <li>Score: {{ post.Score }}</li>
                {% if post.FileSize != "" %}<li>Size: {{ post.FileSize }}</li>{% endif %}
                <li>Views: 0</li>
                <li>Tag History</li>
            </ul>
        </div>
        <h3>Actions</h3>
        <div class="actionbox">
            <ul class="actionlist">
                <li><a href="/gallery/post/edit/{{ post.PostId }}/" onclick="return toggleEdit();">Edit</a></li>
                <li>Flag for deletion</li>
                <li>Add to Favorites</li>
                <li><a id="poolaction" href="#"></a><span id="poolactionworking" hidden>Processing...</span></li>
            </ul>
        </div>
        <div class="pooleditbox">
            <input id="pooleditfield" type="textfield" />
            <ul id="poolautocomplete">
            </ul>
        </div>
    </div>
    <div class="mainpanel">
        <p>
            {% if previewUrl==downloadUrl %}
                <img class="previewImg" src="{{ previewUrl }}" />
            {% else %}
                <a href="{{ downloadUrl }}"><img class="previewImg" src="{{ previewUrl }}" /></a>
            {% endif %}
        </p>
        <p>
            <a href="/gallery/post/edit/{{ post.PostId }}/" onclick="return toggleEdit();">Edit</a> | <a href="{{ downloadUrl }}">Download</a>
        </p>
        <div class="posteditbox">
            <a id="editanchor" />
            <form action="/gallery/edit/" method="POST">
                <input type="hidden" name="post" value="{{ post.PostId }}" />
                <label class="formlabel">Rating</label>         <input name="rating" type="radio"{% if post.Rating=='e' %} checked{% endif %} value="e" /><label>Explicit</label>
                                                                <input name="rating" type="radio"{% if post.Rating=='q' %} checked{% endif %} value="q" /><label>Questionable</label>
                                                                <input name="rating" type="radio"{% if post.Rating=='s' %} checked{% endif %} value="s" /><label>Safe</label><br />
                <label class="formlabel">Parent</label>         <input id="parent" class="textbox" type="textbox" name="parent" value="{% if post.ParentPostId!=-1 %}{{ post.ParentPostId }}{% endif %}" /><br />
                <label class="formlabel">Source</label>         <input id="imgsource" class="textbox" type="textbox" size=35 name="source" value="{{ post.Source }}" /><br />
                <label class="formlabel">Tags</label>           <textarea id="tags" class="textbox" name="tags">{{ post.tagstring }}</textarea><br />
                <label class="formlabel">Description</label>    <textarea id="desc" class="textbox" name="description">{{ post.Description }}</textarea><br />
                <br />
                <input type="submit" value="Save Changes" />
            </form>
        </div>
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
