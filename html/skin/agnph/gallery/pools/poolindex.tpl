{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/list-style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <style>
        #actionbar {
            display: block;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        #action-list {
            list-style: none;
            padding: 0px;
            margin: 0px;
        }
        #pool-search {
            display: inline;
        }
        #pool-search input {
            float: right;
            margin: 0px;
            margin-top: -4px;
        }
        #newpanel {
            padding: 5px;
            display: inline-block;
        }
        .floatactionpanel {
            position: absolute;
            border: 1px solid rgb(0,0,0);
            background-color: rgb(63, 127, 255);
            box-shadow: 5px 5px 8px #07162D;
        }
    </style>
{% endblock %}

{% block scripts %}
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
                        <td><div><a href="/gallery/post/?search={{ pool.searchName }}">{{ pool.Name }}</a></div></td>
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
        No pools found.
    {% endif %}
{% endblock %}
