{% extends 'fics/base.tpl' %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/fics/edit-style.css" />
{% endblock %}

{% use 'fics/storyblock.tpl' %}
{% use 'fics/edit/editchapterblock.tpl' %}

{% block scripts %}
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    {% if create or not chapters %}
        <script type="text/javascript">
            $(document).ready(function() {
                {##### This and CheckTags are duplicated below #####}
                $("#tagbox").keyup(function() {
                    CheckTags(this.value);
                });
                CheckTags($("#tagbox").val());
            });
            function CheckTags(value) {
                if (!(value.toLowerCase().indexOf("{# fe #}male") > -1 ||
                      value.toLowerCase().indexOf("herm") > -1 ||
                      value.toLowerCase().indexOf("sexless") > -1)) {
                    $("#gender-warning").show();
                } else {
                    $("#gender-warning").hide()
                }
            }
        </script>
    {% endif %}
    {% if not create and chapters %}
        <script src="{{ skinDir }}/scripts/jquery.sortable.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $('.sortable').sortable().bind('sortupdate', Update);
                $(".reorder_hint").removeClass("hidden");
                $('.sortable').removeAttr("style");
                $('.sortable').css("cursor", "ns-resize");
                $("#tagbox").keydown(function(e) {
                    if (e.keyCode == 13) {
                        $(this.form).submit();
                        return false;
                    }
                });
                {##### This and CheckTags are duplicated above #####}
                $("#tagbox").keyup(function() {
                    CheckTags(this.value);
                });
                CheckTags($("#tagbox").val());
            });
            function CheckTags(value) {
                if (!(value.toLowerCase().indexOf("{# fe #}male") > -1 ||
                      value.toLowerCase().indexOf("herm") > -1 ||
                      value.toLowerCase().indexOf("sexless") > -1)) {
                    $("#gender-warning").show();
                } else {
                    $("#gender-warning").hide()
                }
            }
            function Update() {
                $('.sortable').sortable('destroy');
                $('.sortable').removeAttr("style");
                $('.sortable').css("opacity", "0.5");
                var index = 1;
                var bestOldIndex = 1;
                var bestNewIndex = 1;
                var bestId = "";
                $(this).children().each(function() {
                    var oldindex = $(this).children(".chapternum")[0].value;
                    var id = $(this).children(".chapterid")[0].value;
                    if (oldindex != index) {
                        var diff = Math.abs(oldindex - index);
                        if (diff > Math.abs(bestOldIndex - bestNewIndex)) {
                            bestOldIndex = oldindex;
                            bestNewIndex = index;
                            bestId = id;
                        }
                    }
                    index++;
                });
                if (bestOldIndex != bestNewIndex && bestId != "") {
                    {# Perform AJAX request #}
                    $.ajax("/fics/story/chapter/order/", {
                        data: {
                            sid: {{ formstory.StoryId }},
                            oldnum: bestOldIndex,
                            newnum: bestNewIndex,
                            id: bestId
                        },
                        method: "POST",
                        success: function(e) {
                            var index = 1;
                            $('.sortable').children().each(function() {
                                {# Update input index, and edit/delete links #}
                                $(this).children(".chapternum").val(index);
                                $(this).find(".chaptereditlink").attr("href", "/fics/edit/{{ formstory.StoryId }}/" + index + "/");
                                $(this).find(".chapterdeletelink").attr("href", "/fics/delete/{{ formstory.StoryId }}/" + index + "/");
                                index++;
                            });
                            $('.sortable').sortable();
                            $('.sortable').removeAttr("style");
                            $('.sortable').css("cursor", "ns-resize");
                        },
                        error: function(e) {
                            $('.sortable').sortable('destroy');
                            alert("Error updating chapter order. Please save story changes and refresh the page.");
                        }
                    });
                } else {
                    {# Nothing changed! #}
                    $('.sortable').sortable();
                    $('.sortable').removeAttr("style");
                    $('.sortable').css("cursor", "ns-resize");
                }
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
            resize: false,
            menubar: false
        });
        tinymce.init({
            selector: "textarea#notes",
            plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
            target_list: [ {title: 'New page', value: '_blank'} ],
            toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
            contextmenu: "image link | hr",
            autoresize_max_height: 150,
            resize: false,
            menubar: false
        });
        {% if create or not chapters %}
            {{ block('chapterMCESetup') }}
        {% endif %}
    </script>
{% endblock %}

{% block content %}
    {% if not create %}
        {{ block('storyblock') }}
        <hr />
        <h3>Edit Story</h3>
    {% else %}
        <h3>Create Story</h3>
    {% endif %}
    {{ block('banner') }}
    {# Autocomplete off so that hidden inputs in the chapter ordering section don't autofill with previous values #}
    <form method="POST" autocomplete="off" accept-charset="UTF-8">
        <input type="hidden" name="sid" value="{% if create %}-1{% else %}{{ formstory.StoryId }}{% endif %}" />
        <p>
            <label>Title:</label>
            <input type="text" name="title" value="{{ formstory.Title }}" />
        </p>
        {# TODO: Coauthors #}
        <p>
            <label>Summary:</label>
            <textarea id="summary" name="summary">
                {% autoescape false %}
                    {{ formstory.Summary }}
                {% endautoescape %}
            </textarea>
        </p>
        <p>
            <label>Rating: </label>
            <select name="rating">
                {# Obfuscate database values #}
                <option value="1"{% if not formstory or formstory.Rating == 'G' %} selected{% endif %}>G</option>
                <option value="2"{% if formstory.Rating == 'P' %} selected{% endif %}>PG</option>
                <option value="3"{% if formstory.Rating == 'T' %} selected{% endif %}>PG-13</option>
                <option value="4"{% if formstory.Rating == 'R' %} selected{% endif %}>R</option>
                <option value="5"{% if formstory.Rating == 'X' %} selected{% endif %}>XXX</option>
            </select>
            <label>Completed:</label>
            <select name="completed">
                <option value="1"{% if not formstory or not formstory.Completed %} selected{% endif %}>No</option>
                <option value="2"{% if formstory.Completed %} selected{% endif %}>Yes</option>
            </select>
            {% if formstory.canFeature %}
                <label>Featured:</label>
                <select name="featured">
                    <option value="D"{% if formstory.Featured=="D" %} selected{% endif %}>None</option>
                    <option value="F"{% if formstory.Featured=="F" %} selected{% endif %}>Featured</option>
                    <option value="G"{% if formstory.Featured=="G" %} selected{% endif %}>Gold</option>
                    <option value="S"{% if formstory.Featured=="S" %} selected{% endif %}>Silver</option>
                    <option value="Z"{% if formstory.Featured=="Z" %} selected{% endif %}>Bronze</option>
                    <option value="f"{% if formstory.Featured=="f" %} selected{% endif %}>Retired</option>
                    <option value="g"{% if formstory.Featured=="g" %} selected{% endif %}>Retired Gold</option>
                    <option value="s"{% if formstory.Featured=="s" %} selected{% endif %}>Retired Silver</option>
                    <option value="z"{% if formstory.Featured=="z" %} selected{% endif %}>Retired Bronze</option>
                </select>
            {% endif %}
        </p>
        {# TODO: Admin Approval? #}
        <p>
            <label>Story Notes:</label>
            <textarea id="notes" name="notes">
                {% autoescape false %}
                    {{ formstory.StoryNotes }}
                {% endautoescape %}
            </textarea>
        </p>
        <p>
            <label>Story Tags:</label><br />
            <textarea id="tagbox" class="tagbox" name="tags">
                {{ formstory.tagstring }}
            </textarea>
            <span id="gender-warning" class="tag-warning"><br />Tags should include the character's gender or pairing (e.g. Female, Male/Female, Sexless)</span>
        </p>

        {% if edit and chapters %}
            <input type="submit" name="save" value="Save Changes" />
        {% endif %}
        <hr />
        {% if create or not chapters %}
            {# Form to compose new chapter 1 #}
            <h4>Chapter 1</h4>
            {{ block('editchapter') }}
            <input type="submit" name="save" value="Create Story" />
        {% else %}
            <h4>Chapters</h4>
            <div class="reorder_hint hidden">(Drag to reorder)</div>
            <ol class="sortable">
                {% for chapter in chapters %}
                    {# TODO: Add non-JS support (Copy fields on submit) #}
                    <li class="chapter-row">
                        <input class="chapternum" type="hidden" value="{{ chapter.ChapterItemOrder + 1 }}" id="{{ chapter.ChapterItemOrder + 1 }}" />
                        <input class="chapterid" type="hidden" value="{{ chapter.hash }}" />
                        <span>{{ chapter.Title }}</span>
                        <span><a class="chaptereditlink" href="/fics/edit/{{ formstory.StoryId }}/{{ chapter.ChapterItemOrder + 1 }}/">Edit</a></span>
                        {% if chapters|length > 1 %}
                            <span><a class="chapterdeletelink" href="/fics/delete/{{ formstory.StoryId }}/{{ chapter.ChapterItemOrder + 1 }}/">Delete</a></span>
                        {% endif %}
                    </li>
                {% endfor %}
            </ol>
            <div class="newchapter">
                <a href="/fics/create/{{ formstory.StoryId }}/{{ chapters|length + 1 }}/">Add new Chapter</a>
            </div>
        {% endif %}
    </form>
{% endblock %}
