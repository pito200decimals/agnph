{% extends 'gallery/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/gallery/poolindex-style.css" />
{% endblock %}

{% block scripts %}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
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
    <div class="mainpanel">
        <h3>Pools</h3>
        {% if canEditPools %}
        <div id="actionbar">
            <ul class="gallerynav" hidden>
                <li><a href="">New</a></li>
            </ul>
            <div id="newpanel">
                <form action="/gallery/pools/create/" method="POST" accept-charset="UTF-8">
                    <input name="name" type="text" />
                    <input type="submit" value="Create New" />
                </form>
            </div>
        </div>
        {% endif %}
        {% if pools|length > 0 %}
            {# Display pool index. #}
            <table class="pooltable">
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
                            <td><div><a href="/gallery/post/?search=pool%3A{{ pool.PoolId }}">{{ pool.Name }}</a></div></td>
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
            <div class="indexIterator">
                {% autoescape false %}
                {{ postIterator }}
                {% endautoescape %}
            </div>
        {% else %}
            {# No pools here. #}
            No pools found.
        {% endif %}
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
