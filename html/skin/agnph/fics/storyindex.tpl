{% extends 'fics/base.tpl' %}

{% block scripts %}
    {{ parent() }}
    {#
    <script type="text/javascript">
        function is_touch_device() {
            try {
                document.createEvent("TouchEvent");
                return true;
            } catch (e) {
                return false;
            }
        }
        $(document).ready(function() {
            $(".scroll_on_hover").mouseover(function() {
                $(this).stop();
                var parentHeight = $(this).parent().height();
                var height = $(this).height();
                if (parentHeight < height) {
                    var scroll = height - parentHeight;
                    var speed = scroll * 25;
                    $(this).animate({
                        top: -scroll
                    }, speed, "linear");
                }
            });
            $(".scroll_on_hover").mouseout(function() {
                $(this).stop();
                $(this).animate({
                    top: 0
                }, 'slow');
            });
            /*
            if (!is_touch_device()) {
                $(".storyblockinfo").css("overflow", "hidden");
            }
            */
        });
    </script>
    #}
{% endblock %}

{% use 'fics/storyblock.tpl' %}

{% block content %}
    {{ block('banner') }}
    {# Avoid taking up too much vertical space in the story index #}
    {% set restrictSummaryHeight=true %}
    {% if searchTerms %}
        <h3>Search Results: {{ searchTerms }}</h3>
    {% endif %}
    {% if stories|length > 0 %}
        {% for story in stories %}
            {{ block('storyblock') }}
        {% endfor %}
        <div class="Clear">&nbsp;</div>
        <div class="iterator">
            {% autoescape false %}{{ iterator }}{% endautoescape %}
        </div>
    {% else %}
        <p>No stories found</p>
    {% endif %}
{% endblock %}
