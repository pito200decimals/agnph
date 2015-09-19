{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/poolindex-style.css') }}" />
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script type="text/javascript">
        $(document).ready(function() {
            $("#actionbar ul").show();
            $("#newpanel").hide().addClass("floatactionpanel");
            var actionbar = $("#actionbar");
            if (actionbar.length) {
                actionbar.find("ul li").first().click(function() {
                    $("#newpanel").toggle();
                    return false;
                });
            }
        });
    </script>
{% endblock %}

{% block content %}
    <h3>Pools</h3>
    {{ block('banner') }}
    <div id="actionbar">
        <form id="pool-search" method="GET" accept-encoding="UTF-8">
            <input type="text" class="search" name="search" value="" required />
        </form>
        {% if canEditPools %}
            <ul id="action-list" hidden>
                <li><a href="">Create</a></li>
            </ul>
            <div id="newpanel">
                <form action="/gallery/pools/create/" method="POST" accept-charset="UTF-8">
                    <input name="search" type="text" />
                    <input type="submit" value="Create Pool" />
                </form>
            </div>
        {% else %}
            &nbsp;
        {% endif %}
    </div>
    {% if pools|length > 0 %}
        {# Display pool index. #}
        <table class="list-table">
            <thead>
                <tr>
                    <td><div><strong>Name</strong></div></td>
                    <td><div><strong>Number of Posts</strong></div></td>
                    <td><div><strong>Created By</strong></div></td>
                    {% if canEditPools %}<td><div><strong>Actions</strong></div></td>{% endif %}
                </tr>
            </thead>
            <tbody>
                {% for pool in pools %}
                    <tr>
                        <td><div><a href="/gallery/post/?search={{ pool.searchName|url_encode }}">{{ pool.Name }}</a></div></td>
                        <td><div>{{ pool.count }}</div></td>
                        <td><div><a href="/user/{{ pool.creator.UserId }}/gallery/">{{ pool.creator.DisplayName }}</a></div></td>
                        {% if canEditPools %}<td><div>
                            <form action="/gallery/pools/delete/" method="POST" accept-charset="UTF-8">
                                <input type="hidden" name="pool" value="{{ pool.PoolId }}" />
                                <a href="#"><input type="submit" value="Delete" /></a>
                            </form>
                        </div></td>{% endif %}
                    </tr>
                {% endfor %}
            </tbody>
        </table>
        <div class="Clear">&nbsp;</div>
        <div class="iterator">
            {% autoescape false %}{{ postIterator }}{% endautoescape %}
        </div>
    {% else %}
        {# No pools here. #}
        <table class="list-table">
            <thead>
                <tr>
                    <td><div><strong>Name</strong></div></td>
                    <td><div><strong>Number of Posts</strong></div></td>
                    <td><div><strong>Created By</strong></div></td>
                    {% if canEditPools %}<td><div><strong>Actions</strong></div></td>{% endif %}
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3">No pools found</td>
                    {% if canEditPools %}<td></td>{% endif %}
                </tr>
            </tbody>
        </table>
    {% endif %}
{% endblock %}
