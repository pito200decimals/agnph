{% extends 'admin/skin-base.tpl' %}

{% block sub_section_navigation %}
    <li class="selected-admin-tab"><a href="/admin/gallery/">Settings</a></li>
    <li><a href="/admin/gallery/tags/">Tags</a></li>
    <li><a href="/admin/gallery/edit-history/">Edit History</a></li>
    <li><a href="/admin/gallery/description-history/">Description History</a></li>
    <li><a href="/admin/gallery/log/">Log</a></li>
{% endblock %}

{% block styles %}
    {{ parent() }}
    {{ inline_css_asset('/tag-complete-style.css')|raw }}
    <style>
        td {
            vertical-align: top;
            padding-bottom: 10px;
        }
    </style>
{% endblock %}

{% block scripts %}
    {{ parent() }}
    <script src="{{ asset('/scripts/jquery.sortable.js') }}"></script>
    <script>
        function AddReason(reason) {
            if (reason == undefined || reason == null) {
                reason = $("#add-reason-text").val();
            }
            if (reason.length == 0) return;
            $("#add-reason-text").val("");
            $("#flag-reason-list").append($('<li>'+reason+'<input type="hidden" name="flag_reason[]" value="'+reason+'" /><input style="margin-left: 10px;" type="button" value="Delete" onclick="DeleteReason(this)" /></li>'));
            $(".sortable").sortable('destroy').sortable();
        }
        function DeleteReason(e) {
            $(e).parent().remove();
            $(".sortable").sortable('destroy').sortable();
        }
        $(document).ready(function() {
            {% for reason in flag_reasons %}
                AddReason("{{ reason }}");
            {% endfor %}

            $(".sortable").css("cursor", "move");
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
        
        $(document).ready(function() {
            {% for tag in gallery_defaultblacklist_tags %}
                AddGalleryTag('{{ tag.Name }}', '{{ tag.Type|lower }}');
            {% endfor %}
        });
        
        function OnEditSubmit() {
            OnEditSubmitGallery();
            console.log('submit', $('.autocomplete-tags').val());
        }
    </script>
{% endblock %}

{% block content %}
    <h3>Gallery Administrator Control Panel</h3>
    {{ block('banner') }}
    <form action="" method="POST" accept-encoding="UTF-8" onsubmit="OnEditSubmit()">
        <table>
            <tr><td><label>Board for News Posts:</label></td><td><input name="news-posts-board" type="text" value="{{ news_posts_board }}" /></td></tr>
            <tr><td colspan="2">
                <strong>Flag Reasons:</strong>
                <ul id="flag-reason-list" class="sortable"></ul>
                <input type="search" id="add-reason-text" />
                <input type="button" onclick="AddReason()" value="Add Flag Reason" />
            </td></tr>
            <tr><td colspan="2">
                <label class="basic-info-label"><strong>Default Tag Blacklist (for not-logged-in users):</strong></label>
                <ul class="g autocomplete-tag-list"></ul><textarea class="g autocomplete-tags" name="gallery_defaultblacklist" hidden>{{ gallery_defaultblacklist }}</textarea><br />
                <input type="text" class="g textbox autocomplete-tag-input" /><span>&nbsp;</span>
            </td></tr>
        </table>
        <input type="submit" name="submit" value="Save Changes" />
    </form>
{% endblock %}
