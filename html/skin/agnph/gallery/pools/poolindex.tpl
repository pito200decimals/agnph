{% extends 'gallery/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/list-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/gallery/poolindex-style.css') }}" />
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script type="text/javascript">
        $(document).ready(function() {
            $("#actionbar ul").show();
            $("#newpanel").hide().addClass("floatactionpanel");
            $("#create-pool-button").click(function() {
                $("#newpanel").toggle();
                return false;
            });
            $("#newpanel input").blur(function() {
                setTimeout(function() {
                    if (!$("#newpanel").is(":focus")) {
                        $("#newpanel").hide();
                    }
                }, 100);
            });
        });
    </script>
{% endblock %}

{% block content %}
    <h3>Pools</h3>
    {{ block('banner') }}
    <div id="actionbar">
        <div class="list-search-bar">
            <form id="pool-search" method="GET" accept-encoding="UTF-8">
                <div class="search">
                    <input class="search" name="search" value="{{ search }}" type="text" required placeholder="Search Pools" />
                    <input type="submit" class="search-button" value="" />
                </div>
            </form>
        </div>
        {% if canCreatePools %}
            <ul id="action-list" hidden>
                <li><a href="" id="create-pool-button">Create</a></li>
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
    {# Display pool index. #}
    <table class="list-table">
        <thead>
            <tr>
                <td><div><strong>Name</strong></div></td>
                <td><div><strong>Number of Posts</strong></div></td>
                <td><div><strong>Created By</strong></div></td>
                {% if canDeletePools %}<td><div><strong>Actions</strong></div></td>{% endif %}
            </tr>
        </thead>
        <tbody>
            {% if pools|length > 0 %}
                {% for pool in pools %}
                    <tr>
                        <td><div><a href="/gallery/post/?search={{ pool.searchString|url_encode }}">{{ pool.Name }}</a></div></td>
                        <td><div>{{ pool.count }}</div></td>
                        <td><div><a href="/user/{{ pool.creator.UserId }}/gallery/">{{ pool.creator.DisplayName }}</a></div></td>
                        {% if canDeletePools %}<td><div>
                            <form action="/gallery/pools/delete/" method="POST" accept-charset="UTF-8">
                                <input type="hidden" name="pool" value="{{ pool.PoolId }}" />
                                <a href="#"><input type="submit" value="Delete" /></a>
                            </form>
                        </div></td>{% endif %}
                    </tr>
                {% endfor %}
            {% else %}
                <tr>
                    <td></td>
                    <td colspan="2">No pools found</td>
                    {% if canDeletePools %}<td></td>{% endif %}
                </tr>
            {% endif %}
        </tbody>
    </table>
    <div class="Clear">&nbsp;</div>
    <div class="iterator">
        {% autoescape false %}{{ postIterator }}{% endautoescape %}
    </div>
{% endblock %}
