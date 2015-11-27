{% extends 'fics/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/fics/tag-help-style.css') }}" />
{% endblock %}

{% block content %}
    <h3>Fics Search Guide</h3>
    <ul id="tag-help-toc">
        <li><a href="#general">General</a></li>
        <li><a href="#filters">Filters</a></li>
        <li><a href="#sorting">Sorting</a></li>
    </ul>
    <div class="block">
    <a id="general"></a>
    <h4>General</h4>
    <ul class="tag-help-block header">
        <div class="Clear">&nbsp;</div>
        <li class="content">
            <p>
                <strong>{word}</strong> - Searches for stories that contain this word in its tags, title, authors, or summary.
            </p>
        </li>
        <li class="content">
            <p>
                <strong>-{word}</strong> - Searches for stories that do <strong>not</strong> contain this word in its tags, title, authors, or summary.
            </p>
            <div>
                <p>
                    <strong>Example:</strong> "-anthro" - Searches for non-anthro stories.
                </p>
            </div>
        </li>
        <div class="Clear">&nbsp;</div>
    </ul>
    </div>
    <div class="block">
    <a id="filters"></a>
    <h4>Filters</h4>
    <ul class="tag-help-block header">
        <div class="Clear">&nbsp;</div>
        <li class="content">
            <p>
                <strong>rating:{g/pg/pg-13/r/xxx}</strong> - Searches for stories with the corresponding rating.
            </p>
            <div>
                <p>
                    <strong>Example:</strong> "rating:xxx" - Searches for stories with rating XXX.
                </p>
            </div>
        </li>
        <li class="content">
            <p>
                <strong>author:{author}</strong> - Searches for stories written by the given author.
            </p>
        </li>
        <li class="content">
            <p>
                <strong>fav:{user}</strong> - Searches for stories favorited by the given user. If logged in, you can view your own favorites with the term "fav:me".
            </p>
        </li>
        <li class="content">
            <p>
                <strong>featured</strong> - Searches for stories that have been featured.
            </p>
            <p>
                <strong>featured:{gold/silver/bronze}</strong> - Searches for stories that have been featured with the given award.
            </p>
        </li>
        <li class="content">
            <p>
                <strong>completed</strong> - Searches for stories that are complete.
            </p>
            <p>
                <strong>incomplete</strong> - Searches for stories that are not complete.
            </p>
        </li>
        <div class="Clear">&nbsp;</div>
    </ul>
    </div>
    <div class="block">
    <a id="sorting"></a>
    <h4>Sorting</h4>
    <ul class="tag-help-block header">
        <div class="Clear">&nbsp;</div>
        <li class="content">
            <p>
                <strong>order:rating, order:score</strong> - Orders found stories by review score.
            </p>
        </li>
        <li class="content">
            <p>
                <strong>order:views</strong> - Orders found stories by number of views.
            </p>
        </li>
        <li class="content">
            <p>
                <strong>order:length, order:words</strong> - Orders found stories by number of words.
            </p>
        </li>
        <li class="content">
            <p>
                <strong>order:chapters</strong> - Orders found stories by number of chapters.
            </p>
        </li>
        <li class="content">
            <p>
                <strong>order:reviews</strong> - Orders found stories by number of reviews.
            </p>
        </li>
        <li class="content">
            <p>
                <strong>order:published</strong> - Orders found stories by initial publish date.
            </p>
        </li>
        <li class="content">
            <p>
                <strong>order:featured</strong> - Orders found stories by featured award.
            </p>
        </li>
        <div class="Clear">&nbsp;</div>
    </ul>
    </div>
{% endblock %}
