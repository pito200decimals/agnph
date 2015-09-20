{% block reviewready %}
	$('ul.tabs li').click(function(){
		var tab_id = $(this).attr('data-tab');

		$('ul.tabs li').removeClass('current');
		$('.tab-content').removeClass('current');

		$(this).addClass('current');
		$("#"+tab_id).addClass('current');
    });
    {% if user and canComment %}
        $("#commentbutton").click(function() {
            $("#commentbutton").hide();
            $("#commentform").show();
            $("html body").animate(
                { scrollTop: $("#commentform").offset().top },
                { duration: 0,
                  complete: function() {
                    tinyMCE.get("commenttextbox").focus();
                }});
        });
    {% endif %}
    {% if user and canReview %}
        $("#reviewbutton").click(function() {
            $("#reviewbutton").hide();
            $("#reviewform").show();
            $("html body").animate(
                { scrollTop: $("#reviewform").offset().top },
                { duration: 0,
                  complete: function() {
                    tinyMCE.get("reviewtextbox").focus();
                }});
        });
        {% if story.AuthorUserId == user.UserId %}
            $(".authorresponsebutton").click(function() {
                $(".authorresponsebutton").show();
                $(this).hide();
                $("#reviewid").val($(this).next().attr("value"));
                $("#responseform").show();
                tinymce.execCommand("mceRemoveEditor", false, "responsetextbox");
                $("#responseformblock").insertAfter($(this));
                tinymce.init({
                    selector: "#responsetextbox",
                    plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor", "spoiler" ],
                    target_list: [ {title: 'New page', value: '_blank'} ],
                    toolbar: "undo redo | bold italic underline | bullist numlist | image link | code blockquote spoiler",
                    contextmenu: "image link | hr",
                    autoresize_max_height: 150,
                    resize: false,
                    menubar: false,
                    content_css: "{{ asset('/comments-style.css') }}"
                });
                $("html body").animate(
                    { scrollTop: $("#responseform").offset().top },
                    { duration: 0,
                      complete: function() {
                        tinyMCE.get("responsetextbox").focus();
                    }});
            });
        {% endif %}
    {% endif %}
{% endblock %}

{% block reviewMCESetup %}
    tinymce.init({
        selector: "textarea.commenttextbox",
        plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor", "spoiler" ],
        target_list: [ {title: 'New page', value: '_blank'} ],
        toolbar: "undo redo | bold italic underline | bullist numlist | image link | code blockquote spoiler",
        contextmenu: "image link | hr",
        autoresize_max_height: 150,
        resize: false,
        menubar: false,
        content_css: "{{ asset('/comments-style.css') }}"
    });
{% endblock %}

{% use 'includes/comment-block.tpl' %}

{% block review_post_block %}
    <li class="comment">
        <img class="comment-avatarimg" src="{{ review.commenter.avatarURL }}" />
        <div class="commentheader">
            {% for action in review.actions|reverse %}
                <form {% if action.url %}action="{{ action.url }}" {% endif %}class="edit-comment-form" method="POST" accept-charset="UTF-8">
                    <input type="hidden" name="action" value="{{ action.action }}" />
                    <input type="hidden" name="id" value="{{ review.id }}" />
                    <input type="submit" value="{{ action.label }}" {% if action.confirmMsg %}onclick="return confirm('{{ action.confirmMsg }}');" {% endif %}/>
                </form>
            {% endfor %}
            <strong>Reviewer:</strong> <a href="/user/{{ review.commenter.UserId }}/">{{ review.commenter.DisplayName }}</a>{% autoescape false %}{{ review.stars }}{% endautoescape %}<br />
            <strong>Date:</strong> {{ review.date }}{% if review.ChapterId > 0 %} <strong>Chapter:</strong> {{ review.chapterTitle }}{% endif %}
        </div>
        <div class="commenttext">
        {% autoescape false %}{{ review.ReviewText }}{% endautoescape %}
        </div>
        {% if review.AuthorResponseText|length > 0 %}
            <div class="commentresponse">
                Author's Response:<br />
                {% autoescape false %}{{ review.AuthorResponseText }}{% endautoescape %}
            </div>
        {% elseif story.AuthorUserId == user.UserId %}
            <input class="authorresponsebutton" type="button" value="Respond" />
            <input type="hidden" value="{{ review.ReviewId }}" />
        {% endif %}
    </li>
{% endblock %}

{% block reviewblock %}
    <div class="comments">
        {# Top-level tabs #}
        <a id="reviews"></a>
        <ul class="tabs">
            <li class="tab-link{% if defaultcomments %} current{% endif %}" data-tab="tab-comments">Comments ({{ comments|length }})</li>
            <li class="tab-link{% if defaultreviews %} current{% endif %}" data-tab="tab-reviews">Reviews ({{ reviews|length }})</li>
        </ul>

        {# Pane for comments #}
        <div id="tab-comments" class="tab-content{% if defaultcomments %}  current{% endif %}">
            {% if comments|length > 0 %}
                <ul class="comment-list">
                    {% for comment in comments %}
                        {{ block('comment') }}
                    {% endfor %}
                </ul>
                <div class="iterator">
                    {% autoescape false %}{{ commentIterator }}{% endautoescape %}
                </div>
            {% else %}
                <span class="no-comments">No comments posted</span>
            {% endif %}
            {% if user and canComment%}
                <input id="commentbutton" type="button" value="Add Comment"/>
                <form id="commentform" method="POST">
                    <textarea id="commenttextbox" name="text" class="commenttextbox">
                    </textarea>
                    <input type="hidden" name="action" value="comment" />
                    <input type="submit" value="Add Comment" />
                </form>
            {% endif %}
        </div>

        {# Pane for reviews #}
        <div id="tab-reviews" class="tab-content{% if defaultreviews %} current{% endif %}">
            {% if reviews|length > 0 %}
                <ul class="comment-list">
                    {% for review in reviews %}
                        {{ block('review_post_block') }}
                    {% endfor %}
                </ul>
                <div class="iterator">
                    {% autoescape false %}{{ reviewIterator }}{% endautoescape %}
                </div>
            {% else %}
                <span class="no-comments">No reviews posted</span>
            {% endif %}
            {% if user and canReview %}
                <input id="reviewbutton" type="button" value="Add Review" />
                <form id="reviewform" method="POST">
                    <textarea id="reviewtextbox" name="text" class="commenttextbox">
                    </textarea>
                    <label class="metalabel">Stars:</label><select name="score">
                        <option value="0">- - -</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                    </select>/10<br />
                    <input type="hidden" name="action" value="review" />
                    <input type="submit" value="Add Review" />
                </form>
            {% endif %}
        </div>
        {% if story.AuthorUserId == user.UserId %}
            <div id="responseformblock">
                <form id="responseform" method="POST">
                    <textarea id="responsetextbox" name="text" class="commenttextbox">
                    </textarea>
                    <input type="hidden" name="action" value="response" />
                    <input id="reviewid" type="hidden" name="reviewId" value="" />
                    <input type="submit" value="Add Response" />
                </form>
            </div>
        {% endif %}
    </div>
{% endblock %}
