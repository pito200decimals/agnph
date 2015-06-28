{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ skinDir }}/user/style.css" />
{% endblock %}

{% block scripts %}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    {% if canEditBio %}
        <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
        <script type="text/javascript">
            tinymce.init({
                selector: "textarea#edit-bio-text",
                plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
                target_list: [ {title: 'New page', value: '_blank'} ],
                toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
                contextmenu: "image link | hr",
                autoresize_max_height: 200,
                resize: false,
                menubar: false
            });
        </script>
    {% endif %}
    <script type="text/javascript">
        $(document).ready(function() {
            {% if canEditBasicInfo %}
                $('#edit-info-button').click(function() {
                    window.location.href = "/user/{{ profile.user.UserId }}/preferences/";
                });
            {% endif %}
            {% if canEditBio %}
                $('#edit-bio-button').click(ShowEditor);
                $('#edit-bio-button').val("Edit");
                function ShowBio(e) {
                    $('#bio-container').html(e);
                    $('#edit-bio-container').hide();
                    $('#bio-container').show();
                    $('#edit-bio-button').unbind("click");
                    $('#edit-bio-button').click(ShowEditor);
                    $('#edit-bio-button').val("Edit");
                    $('#edit-bio-button').prop('disabled', false);
                }
                function ShowEditor() {
                    tinymce.get('edit-bio-text').getBody().setAttribute('contenteditable', true);
                    tinyMCE.get('edit-bio-text').setContent($('#bio-container').html());
                    $('#edit-bio-container').show();
                    $('#bio-container').hide();
                    $('#edit-bio-button').unbind("click");
                    $('#edit-bio-button').click(SaveBio);
                    $('#edit-bio-button').val("Save");
                    $('#edit-bio-button').prop('disabled', false);
                }
                function SaveBio() {
                    {# Disable text area to prep for ajax #}
                    $('#edit-bio-button').prop('disabled', true);
                    tinymce.get('edit-bio-text').getBody().setAttribute('contenteditable', false);
                    tinyMCE.triggerSave();

                    {# Now issue ajax save #}
                    text = $('#edit-bio-text').val();
                    $.ajax("/user/{{ profile.user.UserId }}/preferences/bio/save/", {
                        data: {
                            bio: text
                        },
                        method: "POST",
                        success: ShowBio,
                        error: function(e) {
                            {# On failure, re-enable text area #}
                            alert("Error saving bio.");
                            tinymce.get('edit-bio-text').getBody().setAttribute('contenteditable', true);
                        }
                    });
                }
            {% endif %}
        });
    </script>
{% endblock %}

{% block sidebar %}
        <h4>Actions</h4>
        <ul>
            <li><a href="/user/{{ user.UserId }}/mail/compose/?to={{ profile.user.DisplayName|url_encode }}">Send a Message</a></li>
            <li>Make Administrator</li>
            <li>Revoke Administrator</li>
        </ul>
{% endblock %}

{% block usercontent %}
    <div class="infoblock">
        <h3>Bio</h3>
        <div id="bio-container">
            {% autoescape false %}
                {{ profile.user.bio }}
            {% endautoescape %}
        </div>
        {% if canEditBio %}
            <div id="edit-bio-container">
                <textarea id="edit-bio-text">
                    {% autoescape false %}
                        {{ profile.user.bio }}
                    {% endautoescape %}
                </textarea>
            </div>
            <input id="edit-bio-button" class="edit-info-button" type="button" value="Edit" />
        {% endif %}
        <div class="Clear">&nbsp;</div>
    </div>
    <div class="infoblock">
        <h3>Basic Info</h3>
        <ul id="basic-info">
            {% if profile.user.ShowDOB %}               <li><span class="basic-info-label">Birthday:</span><span>{{ profile.user.birthday }}</span></li>{% endif %}
            {% if profile.user.Species|length > 0 %}    <li><span class="basic-info-label">Species:</span><span>{{ profile.user.Species }}</span></li>{% endif %}
            {% if profile.user.Title|length > 0 %}      <li><span class="basic-info-label">Title:</span><span>{{ profile.user.Title }}</span></li>{% endif %}
            {% if profile.user.Location|length > 0 %}   <li><span class="basic-info-label">Location:</span><span>{{ profile.user.Location }}</span></li>{% endif %}
            {% if canSeePrivateInfo %}
        </ul>
        <h3>Private Info</h3>
        <ul id="basic-info">
            {% if not profile.user.ShowDOB %}           <li><span class="basic-info-label">Birthday:</span><span>{{ profile.user.birthday }}</span></li>{% endif %}
                                                        <li><span class="basic-info-label">Username:</span><span>{{ profile.user.UserName }}</span></li>
                                                        <li><span class="basic-info-label">Email:</span><span>{{ profile.user.Email }}</span></li>
            {% endif %}
        </ul>
        {% if canEditBasicInfo %}
            <input id="edit-info-button" class="edit-info-button" type="button" value="Edit" />
        {% endif %}
        <div class="Clear">&nbsp;</div>
    </div>
    <div class="infoblock">
        <h3>User Statistics</h3>
        <ul id="basic-info">
            {% if profile.user.numForumPosts > 0 %}     <li><span class="basic-info-label">Forum Posts:</span><span>{{ profile.user.numForumPosts }}</span></li>{% endif %}
            {% if profile.user.numGalleryUploads > 0 %} <li><span class="basic-info-label">Gallery Uploads:</span><span>{{ profile.user.numGalleryUploads }}</span></li>{% endif %}
            {% if profile.user.numFicsStories > 0 %}    <li><span class="basic-info-label">Fic Stories:</span><span>{{ profile.user.numFicsStories }}</span></li>{% endif %}
            {% if profile.user.numOekakiDrawn > 0 %}    <li><span class="basic-info-label">Oekaki Drawn:</span><span>{{ profile.user.numOekakiDrawn }}</span></li>{% endif %}
                                                        <li><span class="basic-info-label">Last Active:</span><span>{{ profile.user.lastVisitDate }}</span></li>
                                                        <li><span class="basic-info-label">Date Registered:</span>{{ profile.user.registerDate }}<span></span></li>
            {% if canSeeAdminInfo %}
        </ul>
        <h3>Admin Info</h3>
        <ul id="basic-info">
                                                        <li><span class="basic-info-label">IP Addresses:</span><span>{{ profile.user.ips }}</span></li>
            {% endif %}
        </ul>
    </div>
{% endblock %}
