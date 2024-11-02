{% extends "user/skin-base.tpl" %}

{% block scripts %}
    {% if canEditBio %}
        <script src="{{ asset('/scripts/tinymce.min.js') }}"></script>
        <script type="text/javascript">
            tinymce.init({
                selector: "textarea#edit-bio-text",
                plugins: [ "paste", "link", "autoresize", "hr", "code", "contextmenu", "emoticons", "image", "textcolor" ],
                target_list: [ {title: 'New page', value: '_blank'} ],
                toolbar: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link | code",
                contextmenu: "image link | hr",
                autoresize_max_height: 300,
                resize: true,
                menubar: false,
                relative_urls: false
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
                $('#save-bio-button').click(SaveBio);
                function ShowBio(e) {
                    $('#bio-container').html(e);
                    $('#save-bio-button').prop('disabled', false);
                    $('#edit-bio-container').hide();
                    $('#save-bio-button').hide();
                    $('#bio-container').show();
                    $('#edit-bio-button').show();
                }
                function ShowEditor() {
                    tinymce.get('edit-bio-text').getBody().setAttribute('contenteditable', true);
                    tinyMCE.get('edit-bio-text').setContent($('#bio-container').html());
                    $('#bio-container').hide();
                    $('#edit-bio-button').hide();
                    $('#edit-bio-container').show();
                    $('#save-bio-button').show();
                }
                function SaveBio() {
                    {# Disable text area to prep for ajax #}
                    $('#save-bio-button').prop('disabled', true);
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
                            $('#save-bio-button').prop('disabled', false);
                            tinymce.get('edit-bio-text').getBody().setAttribute('contenteditable', true);
                        }
                    });
                }
            {% endif %}
        });
    </script>
{% endblock %}

{% block sidebar_actions %}
    <ul>
        {% if canEditBasicInfo %}<li><a href="/user/{{ profile.user.UserId }}/preferences/">Change Avatar</a></li>{% endif %}
        {% if user %}<li><a href="/user/{{ user.UserId }}/mail/compose/?to={{ profile.user.DisplayName|url_encode }}">Send a Message</a></li>{% endif %}
        {% if adminLinks|length > 0 %}
            <br />
        {% endif %}
        {{ block('admin_link_block') }}
        {% if banLinks|length > 0 %}
            <br />
            <script>
                function promptBanReason(id) {
                    var reason = prompt("Enter ban reason:", "");
                    if (reason.length == 0) {
                        alert("Ban reason required");
                        return false;
                    }
                    document.getElementById(id+'-ban-reason').value = reason;
                    return submitBan(id);
                }
                function promptConfirm(id) {
                    if (!confirm("Are you sure you want to delete this account?")) {
                        return false;
                    }
                    return submitBan(id);
                }
                function submitBan(id) {
                    document.getElementById(id+'-ban-form').submit();
                    return true;
                }
            </script>
        {% endif %}
        {% for link in banLinks %}
            <li>
                <form id="{{ link.formId }}-ban-form" action="/user/{{ profile.user.UserId }}/ban/" method="POST" accept-encoding="UTF-8" hidden>
                    {% if link.action %}<input type="hidden" name="action" value="{{ link.action }}" />{% endif %}
                    {% if link.duration %}<input type="hidden" name="duration" value="{{ link.duration }}" />{% endif %}
                    {% if link.needsBanReason %}<input id="{{ link.formId }}-ban-reason" type="hidden" name="reason" value="" />{% endif %}
                </form>
                <a href="#" onclick="{% if link.needsBanReason %}promptBanReason('{{ link.formId }}'){% elseif link.needsConfirmation %}promptConfirm('{{ link.formId }}'){% else %}submitBan('{{ link.formId }}'){% endif %}">
                    {{ link.text }}
                </a>
            </li>
        {% endfor %}
    </ul>
{% endblock %}

{% block usercontent %}
    {% if profile.user.hasBio or canEditBio %}
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
                    <input id="save-bio-button" class="save-info-button" type="button" value="Save" />
                </div>
                <input id="edit-bio-button" class="edit-info-button" type="button" value="Edit" />
                <div class="Clear">&nbsp;</div>
            {% endif %}
        </div>
    {% endif %}
    {% if profile.user.hasBasicInfo %}
    <div class="infoblock">
        <h3>Basic Info</h3>
        <ul class="basic-info">
            {% if profile.user.gender|length > 0 %}     <li><span class="basic-info-label">Gender:</span><span>{{ profile.user.gender }}</span></li>{% endif %}
            {% if profile.user.Species|length > 0 %}    <li><span class="basic-info-label">Species:</span><span>{% autoescape false %}{{ profile.user.Species }}{% endautoescape %}</span></li>{% endif %}
            {% if profile.user.Title|length > 0 %}      <li><span class="basic-info-label">Title:</span><span>{% autoescape false %}{{ profile.user.Title }}{% endautoescape %}</span></li>{% endif %}
            {% if profile.user.Location|length > 0 %}   <li><span class="basic-info-label">Location:</span><span>{% autoescape false %}{{ profile.user.Location }}{% endautoescape %}</span></li>{% endif %}
            {% if profile.user.ShowLocalTime %}         <li><span class="basic-info-label">Local Time:</span><span>{{ profile.user.currentTime }}</span></li>{% endif %}
            {% if profile.user.ShowDOB %}               <li><span class="basic-info-label">Birthday:</span><span>{{ profile.user.birthday }}</span></li>{% endif %}
            {% if canSeePrivateInfo %}
        </ul>
        <h3>Private Info</h3>
        <ul class="basic-info">
            {% if not profile.user.ShowDOB %}           <li><span class="basic-info-label">Birthday:</span><span>{{ profile.user.birthday }}</span></li>{% endif %}
            {% if not profile.user.ShowLocalTime %}     <li><span class="basic-info-label">Local Time:</span><span>{{ profile.user.currentTime }}</span></li>{% endif %}
                                                        <li><span class="basic-info-label">Username:</span><span>{{ profile.user.UserName }}</span></li>
                                                        <li><span class="basic-info-label">Email:</span><span>{{ profile.user.Email }}</span></li>
            {% endif %}
        </ul>
        {% if canEditBasicInfo %}
            <input id="edit-info-button" class="edit-info-button" type="button" value="Edit" />
        {% endif %}
    </div>
    {% endif %}
    <div class="infoblock">
        <h3>User Statistics</h3>
        <ul class="basic-info">
            {% if profile.user.numForumPosts > 0 %}     <li><span class="basic-info-label">Forum Posts:</span><span>{{ profile.user.numForumPosts }}</span></li>{% endif %}
            {% if profile.user.numGalleryUploads > 0 %} <li><span class="basic-info-label">Gallery Uploads:</span><span>{{ profile.user.numGalleryUploads }}</span></li>{% endif %}
            {% if profile.user.numFicsStories > 0 %}    <li><span class="basic-info-label">Fic Stories:</span><span>{{ profile.user.numFicsStories }}</span></li>{% endif %}
            {% if profile.user.numOekakiDrawn > 0 %}    <li><span class="basic-info-label">Oekaki Drawn:</span><span>{{ profile.user.numOekakiDrawn }}</span></li>{% endif %}
            {% if profile.user.lastVisitDate %}         <li><span class="basic-info-label">Last Active:</span><span>{{ profile.user.lastVisitDate }}</span></li>{% endif %}
                                                        <li><span class="basic-info-label">Date Registered:</span>{{ profile.user.registerDate }}<span></span></li>
        </ul>
        {% if canSeeAdminInfo %}
        <h3>Admin Info</h3>
        <ul class="basic-info">
                                                        <li><span class="basic-info-label">IP Addresses:</span><span><ul>{% for ip in profile.user.ips %}<li>{{ ip }}</li>{% endfor %}</ul></span></li>
                                                        {% if profile.user.isBanned %}
                                                            <li><span class="basic-info-label">Ban duration:</span><span>{{ profile.user.banDuration }}</span></li>
                                                        {% endif %}
        </ul>
        {% endif %}
    </div>
{% endblock %}
