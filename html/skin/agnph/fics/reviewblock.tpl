{% block reviewready %}
	$('ul.tabs li').click(function(){
		var tab_id = $(this).attr('data-tab');

		$('ul.tabs li').removeClass('current');
		$('.tab-content').removeClass('current');

		$(this).addClass('current');
		$("#"+tab_id).addClass('current');
    });
{% endblock %}

{% block reviewblock %}
{% if comments|length > 0 or reviews|length > 0 %}
<div class="comments">
	<ul class="tabs">
        {% if comments|length > 0 %}
            <li class="tab-link current" data-tab="tab-comments">Comments</li>
        {% endif %}
        {% if reviews|length > 0 %}
            <li class="tab-link{% if comments|length == 0%} current{% endif %}" data-tab="tab-reviews">Reviews</li>
        {% endif %}
	</ul>
    {% if comments|length > 0 %}
        <div id="tab-comments" class="tab-content current">
            <ul>
                {% for comment in comments %}
                    <li>
                        {{ comment.ReviewText }}
                    </li>
                {% endfor %}
            </ul>
        </div>
    {% endif %}
    {% if reviews|length > 0 %}
        <div id="tab-reviews" class="tab-content{% if comments|length == 0%} current{% endif %}">
            <ul>
                {% for review in reviews %}
                    <li>
                        {{ review.ReviewText }}
                    </li>
                {% endfor %}
            </ul>
        </div>
    {% endif %}
</div>
{% endif %}
{% endblock %}
