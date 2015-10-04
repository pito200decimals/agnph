{% extends 'gallery/base.tpl' %}

{% block scripts %}
    {{ parent() }}
{% endblock %}

{% block content %}
    <h3>Gallery Search Guide</h3>
    <ul>
        <li>
            tag
        </li>
        <li>
            ~tag
        </li>
        <li>
            -tag
        </li>
    </ul>
    <ul>
        <li>
            rating:s/q/e
        </li>
        <li>
            user:{user}
        </li>
        <li>
            fav:{user}
        </li>
        <li>
            id:{post id}
        </li>
        <li>
            md5:{md5}
        </li>
        <li>
            parent:{post id}
        </li>
        <li>
            pool:{pool id}
        </li>
        <li>
            file:{extension}
        </li>
        <li>
            source:{source}
        </li>
        <li>
            missing_artist
        </li>
    </ul>
    <ul>
        <li>
            order:date
        </li>
        <li>
            order:age
        </li>
        <li>
            order:fav
        </li>
    </ul>
// order:date (Recent to Past)
// order:age (Oldest to Newest)
// order:fav (Most to Least favorites)
{% endblock %}
