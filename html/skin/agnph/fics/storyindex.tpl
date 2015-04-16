{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/storyindex-style.css" />
{% endblock %}

{% block scripts %}
    {#
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
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

{% block ficscontent %}
    <div style="padding: 5px;">
        {% for story in stories %}
            {{ block('storyblock') }}
        {% endfor %}
    </div>
    <div>
        {% autoescape false %}{{ iterator }}{% endautoescape %}
    </div>
{% endblock %}
