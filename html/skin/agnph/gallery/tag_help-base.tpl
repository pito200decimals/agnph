{% extends 'gallery/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/index-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/tag-help-style.css') }}" />
{% endblock %}

{% block content %}
    <h3>Gallery Search Guide</h3>
    <ul class="tag-toc-list">
        <li><a href="#general">General</a></li>
        <li><a href="#filters">Filters</a></li>
        <li><a href="#sorting">Sorting</a></li>
    </ul>
    <div class="block">
        <a id="general"></a>
        <div class="header">General</div>
        <div class="content">
            <ul class="tag-term-list">
                <li>
                    <p>
                        <strong>{tag}</strong> - Searches for posts that contain this tag.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>~{tag}</strong> - Searches for posts that contain at least one of these tags.
                    </p>
                    <div class="example">
                        <p>
                            <strong>Example:</strong> "~pikachu ~raichu" - Searches for pikachu OR raichu.
                        </p>
                    </div>
                </li>
                <li>
                    <p>
                        <strong>-{tag}</strong> - Searches for posts that do <strong>not</strong> contain this tag.
                    </p>
                    <div class="example">
                        <p>
                            <strong>Example:</strong> "-anthro" - Searches for non-anthro posts.
                        </p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="block">
        <a id="filters"></a>
        <div class="header">Filters</div>
        <div class="content">
            <ul class="tag-term-list">
                <li>
                    <p>
                        <strong>rating:{s/q/e}</strong> - Searches for posts with the rating "safe", "questionable" or "explicit".
                    </p>
                    <div class="example">
                        <p>
                            <strong>Example:</strong> "rating:e" - Searches for only explicit posts.
                        </p>
                    </div>
                </li>
                <li>
                    <p>
                        <strong>user:{user}</strong> - Searches for posts uploaded by the given user.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>fav:{user}</strong> - Searches for posts favorited by the given user. If logged in, you can view your own favorites with the term "fav:me".
                    </p>
                </li>
                <li>
                    <p>
                        <strong>id:{post id}</strong> - Searches for a single post with the given id.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>md5:{md5}</strong> - Searches for posts whose file has the given md5 hash. This is useful for finding if an image is already uploaded, if you already know the md5 hash.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>parent:{post id}</strong> - Searches for posts whose parent post is the given id.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>pool:{pool name}</strong> - Fetches posts in the given pool.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>file:{extension}</strong> - Searches for posts with the given file extension.
                    </p>
                    <div class="example">
                        <p>
                            <strong>Example:</strong> "file:swf" - Searches only for swf flash files.
                        </p>
                    </div>
                </li>
                <li>
                    <p>
                        <strong>width:/height:</strong> - Searches for posts that match the given image sizes.
                    </p>
                    <div class="example">
                        <p>
                            <strong>Example:</strong> "width:1280" - Searches only for images with width 1280px.
                        </p>
                        <p>
                            <strong>Example:</strong> "height:>1000" - Searches only images with height > 1000px.
                        </p>
                    </div>
                </li>
                <li>
                    <p>
                        <strong>score:/views:</strong> - Searches for posts with the given score/view counts.
                    </p>
                    <div class="example">
                        <p>
                            <strong>Example:</strong> "score:10" - Searches only for posts with score exactly 10.
                        </p>
                        <p>
                            <strong>Example:</strong> "views:>1000" - Searches only posts with more than 1000 views.
                        </p>
                    </div>
                </li>
                <li>
                    <p>
                        <strong>missing_artist</strong> - Searches for posts that do not have an artist tag.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>missing_species</strong> - Searches for posts that do not have a species tag.
                    </p>
                </li>
            </ul>
        </div>
    </div>
    <div class="block">
        <a id="sorting"></a>
        <div class="header">Sorting</div>
        <div class="content">
            <ul class="tag-term-list">
                <li>
                    <p>
                        <strong>order:popular</strong> - Orders found posts by overall popularity.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>order:score</strong> - Orders found posts from most-favorited to least-favorited.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>order:views</strong> - Orders found posts from most-viewed to least-viewed.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>order:date</strong> - Orders found posts from newest to oldest.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>order:age</strong> - Orders found posts from oldest to newest.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>order:fav</strong> - Orders found posts from most favorited to least favorited.
                    </p>
                </li>
            </ul>
        </div>
    </div>
{% endblock %}
