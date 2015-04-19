{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/edit-style.css" />
{% endblock %}

{% block scripts %}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    {% if not create and chapters %}
        <script src="{{ skinDir }}/scripts/jquery.sortable.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $('.sortable').sortable().bind('sortupdate', Update);
                $(".reorder_hint").removeClass("hidden");
                $(".mce-i-fullscreen").parent().onClick(function() {
                    alert("TEST");
                    ScrollToChapter();
                });
            });
            function Update() {
            /*
                $('.sortable').sortable('destroy');
                $('.sortable').css("opacity", "0.5");
                var index = 1;
                var changed = [];
                $('.dragitem').each(function() {
                    var id = $('.postid', this)[0].value;
                    var oldindex = $('.postorder', this)[0].value;
                    var myindex = index++;
                    if (oldindex != myindex) {
                        changed.push({
                            postid: id,
                            oldindex: oldindex,
                            newindex: myindex
                        });
                    }
                });
                if (changed.length > 0) {
                    $.ajax("/gallery/pools/reorder/{{ poolId }}/", {
                        data: {
                            values: changed
                        },
                        method: "POST",
                        success: function(e) {
                            $(changed).each(function() {
                                var id = this.postid;
                                var newindex = this.newindex;
                                $('.dragitem').each(function() {
                                    if ($('.postid', this)[0].value == id) {
                                        $('.postorder', this)[0].value = newindex;
                                    }
                                });
                            });
                            $('.sortable').sortable();
                            $('.sortable').removeAttr("style");
                        },
                        error: function(e) {
                            $('.sortable').sortable('destroy');
                            location.reload();
                        }
                    });
                }*/
            }
        </script>
    {% endif %}
    <script type="text/javascript">
        tinymce.init({
            selector: "textarea#summary",
            plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
            target_list: [ {title: 'New page', value: '_blank'} ],
            toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
            contextmenu: "image link | hr",
            autoresize_max_height: 200,
            resize: false
        });
        tinymce.init({
            selector: "textarea#notes",
            plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
            target_list: [ {title: 'New page', value: '_blank'} ],
            toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
            contextmenu: "image link | hr",
            autoresize_max_height: 150,
            resize: false
        });
        {% if create or not chapters %}
            {{ block('chapterMCESetup') }}
        {% endif %}
    </script>
{% endblock %}

{% use 'fics/storyblock.tpl' %}
{% use 'fics/edit/editchapterblock.tpl' %}

{% block ficscontent %}
    {% if not create %}
        {{ block('storyblock') }}
        <hr />
        <h3>Edit Story</h3>
    {% else %}
        <h3>Create Story</h3>
    {% endif %}
    <div>
        {% if errmsg and errmsg|length > 0 %}
            <div class="errormsg">
                Error: {{ errmsg }}
            </div>
        {% endif %}
        <form action="" method="POST">
            <input type="hidden" name="sid" value="{% if create %}-1{% else %}{{ formstory.StoryId }}{% endif %}" />
            <p><label>Title:</label><input type="textfield" name="title" value="{{ formstory.Title }}" /></p>
            {# TODO: Coauthors #}
            <p><label>Summary:</label>
            <textarea id="summary" name="summary">
                {% autoescape false %}
                    {{ formstory.Summary }}
                {% endautoescape %}
            </textarea></p>
            <p><label>Rating: </label><select name="rating">
                {# Obfuscate database values #}
                <option value="1"{% if not formstory or formstory.Rating == 'G' %} selected{% endif %}>G</option>
                <option value="2"{% if formstory.Rating == 'P' %} selected{% endif %}>PG</option>
                <option value="3"{% if formstory.Rating == 'T' %} selected{% endif %}>PG-13</option>
                <option value="4"{% if formstory.Rating == 'R' %} selected{% endif %}>R</option>
                <option value="5"{% if formstory.Rating == 'X' %} selected{% endif %}>XXX</option>
            </select><label>Completed:</label><select name="completed">
                <option value="1"{% if not formstory or not formstory.Completed %} selected{% endif %}>No</option>
                <option value="2"{% if formstory.Completed %} selected{% endif %}>Yes</option>
            </select></p>
            {# TODO: Admin Approval #}
            {# TODO: Series selection #}
            <p><label>formstory Notes:</label>
            <textarea id="notes" name="notes">
                {% autoescape false %}
                    {{ formstory.StoryNotes }}
                {% endautoescape %}
            </textarea></p>

            {% if edit and chapters %}
                <input type="submit" name="save" value="Save Changes" />
            {% endif %}
            <hr />
            <div>
                {% if create or not chapters %}
                    <h4>Chapter 1</h4>
                    {{ block('editchapter') }}
                    <input type="submit" name="save" value="Create Story" />
                {% else %}
                        <h4>Chapters</h4>
                        <div class="reorder_hint hidden">(Drag to reorder)</div>
                        <ol class="sortable">
                            {% for chapter in chapters %}
                                {# TODO: Add non-JS support (Copy fields on submit) #}
                                <li>
                                    <input class="chapterindex" type="hidden" value="{{ chapter.ChapterItemOrder }}" />
                                    {{ chapter.Title }} <a href="/fics/edit_chapter.php?sid={{ formstory.StoryId }}&index={{ chapter.ChapterItemOrder }}">Edit</a> <a href="/fics/edit_chapter.php?action=delete&sid={{ formstory.StoryId }}&index={{ chapter.ChapterItemOrder }}">Delete</a>
                                </li>
                            {% endfor %}
                        </ol>
                        <div class="newchapter">
                            <a href="">Add new Chapter</a>
                        </div>
                {% endif %}
            </div>
        </form>
    </div>
{% endblock %}
