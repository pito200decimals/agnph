{% extends "user/base.tpl" %}

{% block styles %}
    <link rel="stylesheet" type="text/css" href="{{ asset('/user/style.css') }}" />
{% endblock %}

{% block scripts %}
    {{ parent() }}
    {% if canEditBio %}
        <script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
        <script type="text/javascript">
            tinymce.init({
                selector: "textarea#signature",
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
            HideSection($('.expand-click'));
            function ShowSection(ex) {
                ex.unbind("click");
                ex.find('.twiddle').html("<h3>-</h3>");
                ex.next().show();
                ex.click(function() {
                    HideSection($(this));
                });
            }
            function HideSection(ex) {
                ex.unbind("click");
                ex.find('.twiddle').html("<h3>+</h3>");
                ex.next().hide();
                ex.click(function() {
                    ShowSection($(this));
                });
            }
        });
    </script>
{% endblock %}

{% block sidebar %}
    <h4>Actions</h4>
    <ul>
        <li><a href="/user/account/link/">Link Old AGNPH Account</a></li>
    </ul>
{% endblock %}

{% block usercontent %}
    <form action="" method="POST" enctype="multipart/form-data" accept-charset="UTF=8">
        <div class="infoblock">
            <h3>Basic Info</h3>
            <ul id="basic-info">
                <li><span class="basic-info-label">Displayed Name:</span>         <span><input type="text" name="display-name" value="{{ profile.user.DisplayName }}" /></span></li>
                <li><span class="basic-info-label">Gender:</span>           <span>
                    <select name="gender">
                        <option value=""{% if profile.user.Gender == 'U' %} selected{% endif %}>- - -</option>
                        <option value="male"{% if profile.user.Gender == 'M' %} selected{% endif %}>Male</option>
                        <option value="female"{% if profile.user.Gender == 'F' %} selected{% endif %}>Female</option>
                        <option value="other"{% if profile.user.Gender == 'O' %} selected{% endif %}>Other</option>
                    </select></span></li>
                <li><span class="basic-info-label">Birthday:</span>         <span><input type="date" name="dob" value="{{ profile.user.DOB }}" /></span>
                    <span><input type="checkbox" name="show-dob" value="show"{% if profile.user.ShowDOB %} checked{% endif %} />Show Birthday</span></li>
                <li><span class="basic-info-label">Species:</span>          <span><input type="text" name="species" value="{{ profile.user.Species }}" /></span></li>
                <li><span class="basic-info-label">Title:</span>            <span><input type="text" name="title" value="{{ profile.user.Title }}" /></span></li>
                <li><span class="basic-info-label">Location:</span>         <span><input type="text" name="location" value="{{ profile.user.Location }}" /></span></li>
                <li><span class="basic-info-label">Timezone:</span>         <span><input type="text" name="timezone" value="{{ profile.user.timezoneOffset }}" /></span>
                    <span><input type="checkbox" name="auto-detect-timezone" value="yes" {% if user.AutoDetectTimezone %}checked {% endif %}/>Auto-Detect Timezone</span></li>
                <li><span class="basic-info-label">Upload Avatar:</span>    <span><input type="file" name="file" accept="image/jpeg,image/png,image/gif" /></span>
                    <span><input type="checkbox" name="reset-avatar" value="yes" />Reset Avatar</span></li>
            </ul>
            <div class="Clear">&nbsp;</div>
        </div>
        <div class="infoblock">
            <div class="expand-click">
                <span class="twiddle"><h3>+</h3></span>
                <h3>Account Settings</h3>
            </div>
            <ul id="basic-info">
                <li><span class="basic-info-label">Group PM's:</span>           <span><input type="checkbox" name="group-pm" value="group"{% if profile.user.GroupMailboxThreads %} checked{% endif %} /></span></li>
                <li><span class="basic-info-label">Hide online status:</span>   <span><input type="checkbox" name="hide-online" value="hide"{% if profile.user.HideOnlineStatus %} checked{% endif %} /></span></li>
                <li><span class="basic-info-label">Site skin:</span>            <span><select name="skin">
                    {% for skinName in availableSkins %}
                        <option value="{{ skinName }}"{% if skin == skinName %} selected{% endif %}>{{ skinName }}</option>
                    {% endfor %}
                </select></span></li>
            </ul>
        </div>
        <div class="infoblock">
            <div class="expand-click">
                <span class="twiddle"><h3>+</h3></span>
                <h3>Security Settings</h3>
            </div>
            <ul id="basic-info">
                <li><span class="basic-info-label">Username:</span>         <span>{{ profile.user.UserName }}</span></li>
                <li><span class="basic-info-label">Email:</span>            <span><input type="text" name="email" value="{{ profile.user.Email }}" /></span></li>
                <li><span class="basic-info-label">New Password:</span>     <span><input type="password" name="password" value="" /></span></li>
                <li><span class="basic-info-label">Retype Password:</span>  <span><input type="password" name="password-confirm" value="" /></span></li>
            </ul>
        </div>
        <div class="infoblock">
            <div class="expand-click">
                <span class="twiddle"><h3>+</h3></span>
                <h3>Forums Settings</h3>
            </div>
            <ul id="basic-info">
                <li><span class="basic-info-label">Threads per Page:</span><span><input type="text" name="forums-threads-per-page" value="{{ profile.user.ForumThreadsPerPage }}" /></span></li>
                <li><span class="basic-info-label">Posts per Page:</span><span><input type="text" name="forums-posts-per-page" value="{{ profile.user.ForumPostsPerPage }}" /></span></li>
                <li><span class="basic-info-label">Signature:</span><br /><span><textarea name="signature" id="signature">{{ profile.user.Signature }}</textarea></span></li>
            </ul>
        </div>
        <div class="infoblock">
            <div class="expand-click">
                <span class="twiddle"><h3>+</h3></span>
                <h3>Gallery Settings</h3>
            </div>
            <ul id="basic-info">
                <li><span class="basic-info-label">Posts per Page:</span><span><input type="text" name="gallery-posts-per-page" value="{{ profile.user.GalleryPostsPerPage }}" /></span></li>
                <li><span class="basic-info-label">Tag Blacklist:</span><br /><span><textarea name="gallery-tag-blacklist">{{ profile.user.GalleryTagBlacklist }}</textarea></span></li>
                <li><span class="basic-info-label">Enable keyboard pool navigation:</span><span><input type="checkbox" name="gallery-enable-keyboard" value="1" {% if profile.user.NavigateGalleryPoolsWithKeyboard %}checked {% endif %}/></span></li>
                <li><span class="basic-info-label">Disable tagging UI:</span><span><input type="checkbox" name="gallery-plain-tagging" value="1" {% if profile.user.PlainGalleryTagging %}checked {% endif %}/></span></li>
                <li><span class="basic-info-label">Private Favorites:</span><span><input type="checkbox" name="gallery-hide-favorites" value="1" {% if profile.user.PrivateGalleryFavorites %}checked {% endif %}/></span></li>
            </ul>
        </div>
        <div class="infoblock">
            <div class="expand-click">
                <span class="twiddle"><h3>+</h3></span>
                <h3>Fics Settings</h3>
            </div>
            <ul id="basic-info">
                <li><span class="basic-info-label">Stories per Page:</span><span><input type="text" name="fics-stories-per-page" value="{{ profile.user.FicsStoriesPerPage }}" /></span></li>
                <li><span class="basic-info-label">Tag Blacklist:</span><br /><span><textarea name="fics-tag-blacklist">{{ profile.user.FicsTagBlacklist }}</textarea></span></li>
                <li><span class="basic-info-label">Disable tagging UI:</span><span><input type="checkbox" name="fics-plain-tagging" value="1" {% if profile.user.PlainFicsTagging %}checked {% endif %}/></span></li>
                <li><span class="basic-info-label">Private Favorites:</span><span><input type="checkbox" name="fics-hide-favorites" value="1" {% if profile.user.PrivateFicsFavorites %}checked {% endif %}/></span></li>
            </ul>
        </div>
        <div class="infoblock">
            <div class="expand-click">
                <span class="twiddle"><h3>+</h3></span>
                <h3>Oekaki Settings</h3>
            </div>
            <ul id="basic-info">
                <li><span class="basic-info-label">Posts per page:</span><span><input type="text" name="oekaki-posts-per-page" value="N/A" /></span></li>
            </ul>
        </div>
        <input type="submit" value="Save Changes" />
    </form>
{% endblock %}
