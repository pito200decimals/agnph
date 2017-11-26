{% extends 'fics/base.tpl' %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/fics/edit-style.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('/tag-complete-style.css') }}" />
{% endblock %}

{% use 'fics/storyblock.tpl' %}
{% use 'fics/edit/editchapterblock.tpl' %}

{% block scripts %}
    {{ parent() }}
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    {% if not create and chapters %}
        <script src="{{ asset('/scripts/jquery.sortable.js') }}"></script>
        <script>
            $(document).ready(function() {
                var supports_touch = ((document.ontouchstart===null)?true:false);
                if (!supports_touch) {
                    $('.sortable').sortable().bind('sortupdate', Update);
                    $(".reorder_hint").removeClass("hidden");
                    $('.sortable').removeAttr("style");
                    $('.sortable').css("cursor", "move");
                }
                $("#tags").keydown(function(e) {
                    if (e.keyCode == 13) {
                        $(this.form).submit();
                        return false;
                    }
                });
            });
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
                            $('.sortable').css("cursor", "move");
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
    <script src="{{ asset('/scripts/tag-complete.js') }}"></script>
    <script>
        var AddTag;
        var OnEditSubmit;
        (function() {
            var tag_search_url = '/fics/tagsearch/';
            function GetPreclass(pre) {
                var preclass = null;
                if (pre.toLowerCase() == 'category') {
                    preclass = 'atypetag';
                }
                if (pre.toLowerCase() == 'series') {
                    preclass = 'btypetag';
                }
                if (pre.toLowerCase() == 'character') {
                    preclass = 'ctypetag';
                }
                if (pre.toLowerCase() == 'species') {
                    preclass = 'dtypetag';
                }
                if (pre.toLowerCase() == 'general') {
                    preclass = 'mtypetag';
                }
                if (pre.toLowerCase() == 'warning') {
                    preclass = 'ztypetag';
                }
                return preclass;
            }
            var fns = SetUpTagCompleter(tag_search_url, GetPreclass, ".f");
            AddTag = fns.AddTag;
            OnEditSubmit = fns.OnEditSubmit;
        })();
    </script>
    <script type="text/javascript">
        tinymce.init({
            selector: "textarea#summary",
            plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
            target_list: [ {title: 'New page', value: '_blank'} ],
            toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
            contextmenu: "image link | hr",
            autoresize_max_height: 300,
            resize: true,
            browser_spellcheck: true,
            menubar: false,
            relative_urls: false
        });
        tinymce.init({
            selector: "textarea#notes",
            plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
            target_list: [ {title: 'New page', value: '_blank'} ],
            toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
            contextmenu: "image link | hr",
            autoresize_max_height: 300,
            resize: true,
            browser_spellcheck: true,
            menubar: false,
            relative_urls: false
        });
        {% if create or not chapters %}
            {{ block('chapterMCESetup') }}
        {% endif %}
    </script>
    {% if canSetAuthor or canSetCoAuthors %}
        <script src="{{ asset('/scripts/jquery.autocomplete.min.js') }}"></script>
    {% endif %}
    <script>
        $(document).ready(function() {
            {# Set up ajax lookups #}
            {% if canSetAuthor %}
                $('#author-field').autocomplete({
                    serviceUrl: '/user/search/',
                    onSelect: function(suggestion) {
                        $('#author').val(suggestion.data);
                    },
                    onInvalidateSelection: function() {
                        $('#author').val("");
                    },
                    showNoSuggestionNotice: true,
                    tabDisabled: true,
                    autoSelectFirst: true
                }).blur(function() {
                    if ($('#author').val() == "") {
                        $('#author-field').val("");
                    }
                }).keydown(function(event) {
                    {# Prevent submit on enter press #}
                    if (event.keyCode == 13) {
                        event.preventDefault();
                        return false;
                    }
                });
            {% endif %}
            {% if canSetCoAuthors %}
                $('#coauthor-field').autocomplete({
                    serviceUrl: '/user/search/',
                    onSelect: function(suggestion) {
                        AddCoauthor(suggestion);
                        $('#coauthor-field').val("");
                    },
                    showNoSuggestionNotice: true,
                    tabDisabled: true,
                    autoSelectFirst: true
                }).blur(function() {
                    $('#coauthor-field').val("");
                }).keydown(function(event) {
                    {# Prevent submit on enter press #}
                    if (event.keyCode == 13) {
                        event.preventDefault();
                        return false;
                    }
                });
            {% endif %}
            {% for coauthor in formstory.coauthors %}
                AddCoauthor({ value: "{{ coauthor.DisplayName }}", data: {{ coauthor.UserId }} });
            {% endfor %}
        });
        function AddCoauthor(suggestion) {
            var elem = $('<li></li>');
            elem.append($('<input type="hidden" name="coauthors[]" value="'+suggestion.data+'" />')).append($('<span>'+suggestion.value+'</span>'));
            {% if canSetCoAuthors %}
                var close = $('<a class="remove-coauthor-button">X</a>');
                close.click(function(e) {
                    e.stopPropagation();
                    close.parent().remove();
                    return false;
                });
                elem.append(close);
            {% endif %}
            $('#coauthor-list').append(elem);
        }
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
    <form method="POST" autocomplete="off" enctype="multipart/form-data" accept-charset="UTF-8" onsubmit="OnEditSubmit()">
        <input type="hidden" name="sid" value="{% if create %}-1{% else %}{{ formstory.StoryId }}{% endif %}" />
        <div class="form-block">
            <label>Title:</label>
            <input type="text" name="title" value="{{ formstory.Title }}" />
        </div>
        {% if canSetAuthor %}
            <div class="form-block">
                <input type="hidden" id="author" name="author" value="{{ formstory.author.UserId }}" />
                <label>Author:</label><input type="text" id="author-field" name="author-field" value="{{ formstory.author.DisplayName }}" />
            </div>
        {% else %}
            <input type="hidden" id="author" name="author" value="{{ user.UserId }}" />
        {% endif %}
        <div class="form-block">
            <label>Co-Authors:</label>
            {% if canSetCoAuthors %}
                <input type="text" id="coauthor-field" name="coauthor-field" value="" />
            {% endif %}
            <ul id="coauthor-list">
            </ul>
        </div>
        <div class="form-block">
            <label>Summary:</label>
            <textarea id="summary" name="summary">
                {% autoescape false %}
                    {{ formstory.Summary }}
                {% endautoescape %}
            </textarea>
        </div>
        <div class="form-block">
            <label>Rating:</label>
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
        </div>
        {# TODO: Admin Approval? #}
        <div class="form-block">
            <label>Story Notes:</label>
            <textarea id="notes" name="notes">
                {% autoescape false %}
                    {{ formstory.StoryNotes }}
                {% endautoescape %}
            </textarea>
        </div>
        <div class="form-block">
            <label>Story Tags:</label><br />
            {% if not user.PlainFicsTagging %}
                <script>
                    $(document).ready(function() {
                        {% for tag in story.tags %}
                            AddTag('{{ tag.Name }}', '{{ tag.Type|lower }}');
                        {% endfor %}
                    });
                </script>
                <ul class="f autocomplete-tag-list"></ul><textarea class="f autocomplete-tags" name="tags" hidden>{{ post.tagstring }}</textarea><br />
                <input type="text" class="f textbox autocomplete-tag-input" placeholder="Enter Tag"/><br />
            {% else %}
                <textarea id="tags" class="tagbox" name="tags">{{ formstory.tagstring }}</textarea>
            {% endif %}
        </div>

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
            <ol class="chapter-list sortable">
                {% for chapter in chapters %}
                    {# TODO: Add non-JS support (Copy fields on submit) #}
                    <li>
                        <input class="chapternum" type="hidden" value="{{ chapter.ChapterItemOrder + 1 }}" id="{{ chapter.ChapterItemOrder + 1 }}" />
                        <input class="chapterid" type="hidden" value="{{ chapter.hash }}" />
                        <div class="chapter-row">
                            <span class="chapter-title">{{ chapter.Title }}</span>
                            <span class="chapter-actions">
                                <span class="chapter-stats">
                                    <span class="desktop-only">{{ chapter.WordCount }} words</span>
                                    <span class="desktop-only">{{ chapter.Views }} views</span>
                                </span>
                                <span><a class="chaptereditlink" href="/fics/edit/{{ formstory.StoryId }}/{{ chapter.ChapterItemOrder + 1 }}/">Edit</a></span>
                                {% if chapters|length > 1 %}
                                    <span><a class="chapterdeletelink" href="/fics/delete/{{ formstory.StoryId }}/{{ chapter.ChapterItemOrder + 1 }}/">Delete</a></span>
                                {% endif %}
                            </span>
                            <div class="Clear">&nbsp;</div>
                        </div>
                    </li>
                {% endfor %}
            </ol>
            <div class="newchapter">
                <a href="/fics/create/{{ formstory.StoryId }}/{{ chapters|length + 1 }}/">Add new Chapter</a>
            </div>
        {% endif %}
    </form>
{% endblock %}
