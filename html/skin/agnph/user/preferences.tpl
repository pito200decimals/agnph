{% extends "user/skin-base.tpl" %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/user/preferences-style.css')|raw }}
    {{ inline_css_asset('/tag-complete-style.css')|raw }}
{% endblock %}

{% block scripts %}
    {{ parent() }}
    {% if canEditBio %}
        <script src="{{ asset('/scripts/tinymce.min.js') }}"></script>
        <script type="text/javascript">
            tinymce.init({
                selector: "textarea#signature-input",
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
            $("#keyboard-label").hover(function() {
                $("#keyboard-help").show();
            }, function() {
                $("#keyboard-help").hide();
            }).mousemove(function(e) {
                var mousex = e.clientX + 20;
                var mousey = e.clientY + 10;
                $('#keyboard-help').css({ top: mousey, left: mousex });
            });
        });
    </script>
    <script src="{{ asset('/scripts/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('/scripts/tag-complete.js') }}"></script>
    <script type="text/javascript">
        var AddGalleryTag;
        var OnEditSubmitGallery;
        (function() {
            var tag_search_url = '/gallery/tagsearch/';
            function GetPreclass(pre) {
                var preclass = null;
                if (pre.toLowerCase() == 'artist') {
                    preclass = 'atypetag';
                }
                if (pre.toLowerCase() == 'copyright') {
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
                return preclass;
            }
            var fns = SetUpTagCompleter(tag_search_url, GetPreclass, ".g");
            AddGalleryTag = fns.AddTag;
            OnEditSubmitGallery = fns.OnEditSubmit;
        })();
        var AddFicsTag;
        var OnEditSubmitFics;
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
            AddFicsTag = fns.AddTag;
            OnEditSubmitFics = fns.OnEditSubmit;
        })();
        
        $(document).ready(function() {
            {% for tag in gallery_blacklist_tags %}
                AddGalleryTag('{{ tag.Name }}', '{{ tag.Type|lower }}');
            {% endfor %}
            {% for tag in fics_blacklist_tags %}
                AddFicsTag('{{ tag.Name }}', '{{ tag.Type|lower }}');
            {% endfor %}
        });
        
        function OnEditSubmit() {
            OnEditSubmitGallery();
            OnEditSubmitFics();
        }
    </script>
{% endblock %}

{% block sidebar %}
    <h4>Actions</h4>
    <ul>
        <li><a href="/user/account/link/">Link Old AGNPH Account</a></li>
    </ul>
{% endblock %}

{% block usercontent %}
    <div id="keyboard-help">
        <ul>
            <li>Left/Right: Pool navigation</li>
            <li>E/P - Edit post tags/parent</li>
            <li>D - Download file</li>
            <li>S - Search</li>
            <li>F - Add/Remove Favorites</li>
            <li>L - Add to Pool</li>
        </ul>
    </div>
    <form action="" method="POST" enctype="multipart/form-data" accept-charset="UTF=8" onsubmit="OnEditSubmit()">
        <div class="infoblock">
            <input type="checkbox" id="basic-info-toggle" class="settings-toggle" checked />
            <label for="basic-info-toggle" class="info-section-header"><h3><span class="toggle-label"></span>Basic Info</h3></label>
            <ul class="basic-info">
                <li>
                    <label for="display-name-input" class="basic-info-label">Displayed Name:</label>
                    <input type="text" id="display-name-input" name="display-name" value="{{ profile.user.DisplayName }}" />
                </li>
                <li>
                    <label for="gender-input" class="basic-info-label">Gender:</label>           
                    <select id="gender-input" name="gender">
                        <option value=""{% if profile.user.Gender == 'U' %}{{ " " }}selected{% endif %}>- - -</option>
                        <option value="male"{% if profile.user.Gender == 'M' %}{{ " " }}selected{% endif %}>Male</option>
                        <option value="female"{% if profile.user.Gender == 'F' %}{{ " " }}selected{% endif %}>Female</option>
                        <option value="other"{% if profile.user.Gender == 'O' %}{{ " " }}selected{% endif %}>Other</option>
                    </select>
                </li>
                <li>
                    <label for="dob-input" class="basic-info-label">Birthday:</label>
                    <input type="date" id="dob-input" name="dob" value="{{ profile.user.DOB }}" />
                    <label for="show-dob-input" class="basic-info-label">
                        <input type="checkbox" id="show-dob-input" name="show-dob" value="show"{% if profile.user.ShowDOB %}{{ " " }}checked{% endif %} />
                        Show Birthday
                    </label>
                </li>
                <li>
                    <label for="species-input" class="basic-info-label">Species:</label>
                    <input type="text" id="species-input" name="species" value="{{ profile.user.Species }}" />
                </li>
                <li>
                    <label class="basic-info-label" for="title-input">Title:</label>
                    <input type="text" id="title-input" name="title" value="{{ profile.user.Title }}" />
                </li>
                <li>
                    <label for="location-input" class="basic-info-label">Location:</label>
                    <input type="text" id="location-input" name="location" value="{{ profile.user.Location }}" />
                </li>
                <li>
                    <label for="timezone-input" class="basic-info-label">Timezone:</label>
                    <input type="text" id="timezone-input" name="timezone" value="{{ profile.user.timezoneOffset }}" />
                    <label for="auto-detect-timezone-input" class="basic-info-label">
                        <input type="checkbox" id="auto-detect-timezone-input" name="auto-detect-timezone" value="yes"{% if user.AutoDetectTimezone %}{{ " " }}checked{% endif %}/>
                        Auto-Detect Timezone
                    </label>
                    <label for="show-local-time-input" class="basic-info-label">
                        <input type="checkbox" id="show-local-time-input" name="show-local-time" value="yes"{% if user.ShowLocalTime %}{{ " " }}checked{% endif %}/>
                        Show local time
                    </label>
                </li>
                <li>
                    <label for="file-input" class="basic-info-label">Upload Avatar:</label>
                    <input type="file" id="file-input" name="file" accept="image/jpeg,image/png,image/gif" />
                    <label for="reset-avatar-input" class="basic-info-label">
                        <input type="checkbox" id="reset-avatar-input" name="reset-avatar" value="yes" />
                        Reset Avatar
                    </label>
                </li>
            </ul>
        </div>
        <div class="infoblock">
            <input type="checkbox" id="account-settings-toggle" class="settings-toggle" />
            <label for="account-settings-toggle" class="info-section-header"><h3><span class="toggle-label"></span>Account Settings</h3></label>
            <ul class="basic-info">
                <li>
                    <label for="group-pm-input" class="basic-info-label">Group PM's:</label>
                    <input type="checkbox" id="group-pm-input" name="group-pm" value="group"{% if profile.user.GroupMailboxThreads %}{{ " " }}checked{% endif %} />
                </li>
                <li>
                    <label for="hide-online-input" class="basic-info-label">Hide online status:</label>
                    <input type="checkbox" id="hide-online-input" name="hide-online" value="hide"{% if profile.user.HideOnlineStatus %}{{ " " }}checked{% endif %} />
                </li>
                <li>
                    <label for="skin-input" class="basic-info-label">Site skin:</label>
                    <select id="skin-input" name="skin">
                        {% for skinName in availableSkins %}
                            <option value="{{ skinName }}"{% if profile.user.skin == skinName %}{{ " " }}selected{% endif %}>{{ skinName }}</option>
                        {% endfor %}
                    </select>
                </li>
            </ul>
        </div>
        <div class="infoblock">
            <input type="checkbox" id="security-settings-toggle" class="settings-toggle" />
            <label for="security-settings-toggle" class="info-section-header"><h3><span class="toggle-label"></span>Security Settings</h3></label>
            <ul class="basic-info">
                <li>
                    <label class="basic-info-label">Username:</label>
                    {{ profile.user.UserName }}
                </li>
                <li>
                    <label for="email-input" class="basic-info-label">Email:</label>
                    <input type="text" id="email-input" name="email" value="{{ profile.user.Email }}" />
                </li>
                <li>
                    <label for="password-input" class="basic-info-label">New Password:</label>
                    <input type="password" id="password-input" name="password" value="" />
                </li>
                <li>
                    <label for="password-confirm-input" class="basic-info-label">Retype Password:</label>
                    <input type="password" id="password-confirm-input" name="password-confirm" value="" />
                </li>
            </ul>
        </div>
        <div class="infoblock">
            <input type="checkbox" id="forums-settings-toggle" class="settings-toggle" />
            <label for="forums-settings-toggle" class="info-section-header"><h3><span class="toggle-label"></span>Forums Settings</h3></label>
            <ul class="basic-info">
                <li>
                    <label for="forums-threads-per-page-input" class="basic-info-label">Threads per Page:</label>
                    <input type="text" id="forums-threads-per-page-input" name="forums-threads-per-page" value="{{ profile.user.ForumThreadsPerPage }}" />
                </li>
                <li>
                    <label for="forums-posts-per-page-input" class="basic-info-label">Posts per Page:</label>
                    <input type="text" id="forums-posts-per-page-input" name="forums-posts-per-page" value="{{ profile.user.ForumPostsPerPage }}" />
                </li>
                <li>
                    <label for="signature-input" class="basic-info-label">Signature:</label>
                    <textarea id="signature-input" name="signature">{{ profile.user.Signature }}</textarea>
                </li>
            </ul>
        </div>
        <div class="infoblock">
            <input type="checkbox" id="gallery-settings-toggle" class="settings-toggle" />
            <label for="gallery-settings-toggle" class="info-section-header"><h3><span class="toggle-label"></span>Gallery Settings</h3></label>
            <ul class="basic-info">
                <li>
                    <label for="gallery-posts-per-page-input" class="basic-info-label">Posts per Page:</label>
                    <input type="text" id="gallery-posts-per-page-input" name="gallery-posts-per-page" value="{{ profile.user.GalleryPostsPerPage }}" />
                </li>
                <li>
                    <label for="gallery-tag-blacklist-input" class="basic-info-label">Tag Blacklist:</label>
                    {% if not user.PlainGalleryTagging %}
                        <ul class="g autocomplete-tag-list"></ul><textarea class="g autocomplete-tags" name="gallery-tag-blacklist" hidden>{{ profile.user.GalleryTagBlacklist }}</textarea><br />
                        <input type="text" class="g textbox autocomplete-tag-input" />
                    {% else %}
                        <textarea id="gallery-tag-blacklist-input" name="gallery-tag-blacklist">{{ profile.user.GalleryTagBlacklist }}</textarea>
                    {% endif %}
                </li>
                <li>
                    <label for="gallery-ignore-blacklist-for-pools-input" class="basic-info-label">Ignore Blacklist in Pools:</label>
                    <input type="checkbox" id="gallery-ignore-blacklist-for-pools-input" name="gallery-ignore-blacklist-for-pools" value="1"{% if profile.user.IgnoreGalleryBlacklistForPools %}{{ " " }}checked{% endif %}/>
                </li>
                <li>
                    <label for="gallery-enable-keyboard-input" class="basic-info-label" id="keyboard-label">Enable keyboard shortcuts:</label>
                    <input type="checkbox" id="gallery-enable-keyboard-input" name="gallery-enable-keyboard" value="1"{% if profile.user.NavigateGalleryPoolsWithKeyboard %}{{ " " }}checked{% endif %}/>
                </li>
                <li>
                    <label for="gallery-plain-tagging-input" class="basic-info-label">Disable tagging UI:</label>
                    <input type="checkbox" id="gallery-plain-tagging-input" name="gallery-plain-tagging" value="1"{% if profile.user.PlainGalleryTagging %}{{ " " }}checked{% endif %}/>
                </li>
                <li>
                    <label for="gallery-hide-favorites-input" class="basic-info-label">Private Favorites:</label>
                    <input type="checkbox" id="gallery-hide-favorites-input" name="gallery-hide-favorites" value="1"{% if profile.user.PrivateGalleryFavorites %}{{ " " }}checked{% endif %}/>
                </li>
            </ul>
        </div>
        <div class="infoblock">
            <input type="checkbox" id="fics-settings-toggle" class="settings-toggle" />
            <label for="fics-settings-toggle" class="info-section-header"><h3><span class="toggle-label"></span>Fics Settings</h3></label>
            <ul class="basic-info">
                <li>
                    <label for="fics-stories-per-page-input" class="basic-info-label">Stories per Page:</label>
                    <input type="text" id="fics-stories-per-page-input" name="fics-stories-per-page" value="{{ profile.user.FicsStoriesPerPage }}" />
                </li>
                <li>
                    <label for="fics-tag-blacklist-input" class="basic-info-label">Tag Blacklist:</label>
                    {% if not user.PlainFicsTagging %}
                        <ul class="f autocomplete-tag-list"></ul><textarea class="f autocomplete-tags" name="fics-tag-blacklist" hidden>{{ profile.user.FicsTagBlacklist }}</textarea><br />
                        <input type="text" class="f textbox autocomplete-tag-input" />
                    {% else %}
                        <textarea id="fics-tag-blacklist-input" name="fics-tag-blacklist">{{ profile.user.FicsTagBlacklist }}</textarea>
                    {% endif %}
                </li>
                <li>
                    <label for="fics-plain-tagging-input" class="basic-info-label">Disable tagging UI:</label>
                    <input type="checkbox" id="fics-plain-tagging-input" name="fics-plain-tagging" value="1"{% if profile.user.PlainFicsTagging %}{{ " " }}checked{% endif %}/>
                </li>
                <li>
                    <label for="fics-hide-favorites-input" class="basic-info-label">Private Favorites:</label>
                    <input type="checkbox" id="fics-hide-favorites-input" name="fics-hide-favorites" value="1"{% if profile.user.PrivateFicsFavorites %}{{ " " }}checked{% endif %}/>
                </li>
            </ul>
        </div>
        <p></p>
        <input type="submit" value="Save Changes" />
    </form>
{% endblock %}
