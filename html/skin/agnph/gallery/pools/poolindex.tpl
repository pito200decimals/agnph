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

{% block gallerycontent %}
    <div class="mainpanel">
        <h3>Pools</h3>
        {% if canEditPools %}
        <div id="actionbar">
            <ul class="gallerynav" hidden>
                <li><a href="">New</a></li>
            </ul>
            <div id="newpanel">
                <form action="/gallery/pools/create/" method="POST">
                    <input name="name" type="textfield" />
                    <input type="submit" value="Create New" />
                </form>
            </div>
        </div>
        {% endif %}
        {% if pools|length > 0 %}
            {# Display search index. #}
            <table class="pooltable">
                <tr>
                    <td><strong>Name</strong></td>
                    <td><strong>Number of Posts</strong></td>
                    <td><strong>Created By</strong></td>
                    {% if canEditPools %}<td><strong>Actions</strong></td>{% endif %}
                </tr>
                {% for pool in pools %}
                    <tr>
                        <td><a href="/gallery/post/?search=pool%3A{{ pool.PoolId }}">{{ pool.Name }}</a></td>
                        <td>{{ pool.count }}</td>
                        <td><a href="/user/{{ pool.creator.UserId }}/">{{ pool.creator.DisplayName }}</a></td>
                        {% if canEditPools %}<td>
                            <form action="/gallery/pools/delete/" method="POST">
                                <input type="hidden" name="pool" value="{{ pool.PoolId }}" />
                                <a href="#"><input type="submit" value="Delete" /></a>
                            </form>
                        </td>{% endif %}
                    </tr>
                {% endfor %}
            </table>
            <div class="Clear">&nbsp;</div>
            <div class="indexIterator">
                {% autoescape false %}
                {{ postIterator }}
                {% endautoescape %}
            </div>
        {% else %}
            {# No posts here. #}
            No pools found.
        {% endif %}
    </div>
    <div class="Clear">&nbsp;</div>
{% endblock %}
