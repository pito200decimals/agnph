{% extends "user/skin-base.tpl" %}

{% block styles %}
    {{ parent() }}
    <link rel="stylesheet" type="text/css" href="{{ asset('/user/mail-style.css') }}" />
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <script src="{{ asset('/scripts/tinymce-spoiler-plugin.js') }}"></script>
    <script src="{{ asset('/scripts/jquery.autocomplete.min.js') }}"></script>
    <script type="text/javascript">
        tinymce.init({
            selector: "textarea#message",
            plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor", "spoiler" ],
            target_list: [ {title: 'New page', value: '_blank'} ],
            toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | image link | code blockquote spoiler",
            contextmenu: "image link | hr",
            autoresize_max_height: 300,
            resize: true,
            menubar: false,
            relative_urls: false,
            content_css: "{{ asset('/comments-style.css') }}"
        });
        $(document).ready(function() {
            {# Set up ajax lookups #}
            $('#to-field').autocomplete({
                serviceUrl: '/user/search/',
                onSelect: function(suggestion) {
                    $('#ruid-field').val(suggestion.data);
                },
                onInvalidateSelection: function() {
                    $('#ruid-field').val("");
                },
                showNoSuggestionNotice: true
            });
            {# Prevent submit without user selected #}
            $('#form').submit(function(event){
                if ($('#ruid-field').val() == "") {
                    {# Show banner #}
                    $('#missing-user').show();
                    event.preventDefault();
                    return false;
                }
            });
            {# Prevent submit on enter press #}
            $('#to-field, #subject-field').keydown(function(event){
                if (event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });
    </script>
{% endblock %}

{% block usercontent %}
    <form id="form" action="/user/{{ profile.user.UserId }}/mail/send/" method="POST" accept-charset="UTF-8">
        <ul class="compose-form">
            <li>
                <label>To:</label>
                {% if toUserId != -1 %}
                    <input id="to-field" name="to" type="text" value="{{ toUser }}" />
                {% else %}
                    <input type="text" value="{{ toUser }}" disabled />
                    <input id="to-field" name="to" type="hidden" value="{{ toUser }}" />
                {% endif %}
                <input id="ruid-field" name="ruid" type="hidden" value="{{ toUserId }}" />
                <span id="missing-user" class="compose-error">User missing</span>
            </li>
            <li>
                <label>Subject:</label>
                <input id="subject-field" name="subject" type="text" value="" />
            </li>
            <li>
                <textarea id="message" name="message">
                    {{ message }}
                </textarea>
            </li>
        <input id="submit-button" type="submit" value="Send" />
    </form>
{% endblock %}
