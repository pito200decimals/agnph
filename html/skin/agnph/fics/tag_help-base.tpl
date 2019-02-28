{% extends 'fics/base.tpl' %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/index-style.css')|raw }}
    {{ inline_css_asset('/tag-help-style.css')|raw }}
{% endblock %}

{% block content %}
    <h3>Fics Search Guide</h3>
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
                        <strong>{word}</strong> - Searches for stories that contain this word in its tags, title, authors, or summary.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>-{word}</strong> - Searches for stories that do <strong>not</strong> contain this word in its tags, title, authors, or summary.
                    </p>
                    <div class="example">
                        <p>
                            <strong>Example:</strong> "-anthro" - Searches for non-anthro stories.
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
                        <strong>rating:{g/pg/pg-13/r/xxx}</strong> - Searches for stories with the corresponding rating.
                    </p>
                    <div class="example">
                        <p>
                            <strong>Example:</strong> "rating:xxx" - Searches for stories with rating XXX.
                        </p>
                    </div>
                </li>
                <li>
                    <p>
                        <strong>author:{author}</strong> - Searches for stories written by the given author.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>fav:{user}</strong> - Searches for stories favorited by the given user. If logged in, you can view your own favorites with the term "fav:me".
                    </p>
                </li>
                <li>
                    <p>
                        <strong>featured</strong> - Searches for stories that have been featured.
                    </p>
                    <p>
                        <strong>featured:{gold/silver/bronze}</strong> - Searches for stories that have been featured with the given award.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>completed</strong> - Searches for stories that are complete.
                    </p>
                    <p>
                        <strong>incomplete</strong> - Searches for stories that are not complete.
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
                        <strong>order:rating, order:score</strong> - Orders found stories by review score.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>order:views</strong> - Orders found stories by number of views.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>order:length, order:words</strong> - Orders found stories by number of words.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>order:chapters</strong> - Orders found stories by number of chapters.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>order:reviews</strong> - Orders found stories by number of reviews.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>order:published</strong> - Orders found stories by initial publish date.
                    </p>
                </li>
                <li>
                    <p>
                        <strong>order:featured</strong> - Orders found stories by featured award.
                    </p>
                </li>
            </ul>
        </div>
    </div>
{% endblock %}
