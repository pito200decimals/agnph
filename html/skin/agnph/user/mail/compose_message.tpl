{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/mail-style.css" />
{% endblock %}

{% block scripts %}
    <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="{{ skinDir }}/scripts/jquery.autocomplete.min.js"></script>
    <script type="text/javascript">
        tinymce.init({
            selector: "textarea#message",
            plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
            target_list: [ {title: 'New page', value: '_blank'} ],
            toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
            contextmenu: "image link | hr",
            autoresize_max_height: 200,
            resize: false,
            menubar: false
        });
        $(document).ready(function() {
            {# Set up ajax lookups #}
            $('#to-field').keyup(function() {
                var name = $(this).val();
            });
            $('#to-field').autocomplete({
                serviceUrl: '/user/find_user_ajax.php',
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
    <div>
        <form id="form" action="/user/{{ profile.user.UserId }}/mail/send/" method="POST" accept-charset="UTF-8">
            <ul class="compose-form">
                <li>
                    <label>To:</label>
                    <input id="to-field" name="to" type="text" value="{{ toUser }}" />
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
    </div>
{% endblock %}