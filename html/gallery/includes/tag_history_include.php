<?php
// PHP code that renders a list of tag edits.

// Create item elements.
$all_tag_ids = array();
array_map(function($item) use (&$all_tag_ids) {
    array_map(function($tag_id) use (&$all_tag_ids) {
        if (mb_strlen($tag_id) == 0) return;
        $all_tag_ids[] = $tag_id;
    }, array_merge(explode(",", $item['TagsAdded']), explode(",", $item['TagsRemoved'])));
}, $tag_history_items);

$tags = array();
if (sizeof($all_tag_ids) > 0) {
    $tags = GetTagsById(GALLERY_TAG_TABLE, $all_tag_ids);
    if ($tags == null) RenderErrorPage("Post not found");
}
$rating = "";
$parent = "none";
$source = "";
$tag_history_items = array_reverse($tag_history_items);  // Need to process in reverse order.
foreach ($tag_history_items as &$item) {
    $tag_changes = "";
    $adds = array_map(function($tag_id) use ($tags) {
        $typeclass = mb_strtolower($tags[$tag_id]['Type'])."typetag";
        return "<span class='pscore'>+</span><span class='$typeclass'>".$tags[$tag_id]['Name']."</span>";
    }, array_filter(explode(",", $item['TagsAdded']), "mb_strlen"));
    $removes = array_map(function($tag_id) use ($tags) {
        $typeclass = mb_strtolower($tags[$tag_id]['Type'])."typetag";
        return "<span class='nscore'>-</span><span class='$typeclass'>".$tags[$tag_id]['Name']."</span>";
    }, array_filter(explode(",", $item['TagsRemoved']), "mb_strlen"));
    $edits = array_merge($adds, $removes);
    // Add in rating/source/parent changes.
    $propsChanged = $item['PropertiesChanged'];
    if (mb_strlen($propsChanged) > 0) {
        $props = explode(" ", $propsChanged);
        foreach ($props as $prop) {
            if (startsWith($prop, "rating:")) {
                $r = mb_substr($prop, 7);
                if ($r != $rating) {
                    $edits[] = "<span class='ptypetag'>$prop</span>";
                    $rating = $r;
                }
            } else if (startsWith($prop, "parent:")) {
                $p = mb_substr($prop, 7);
                if ($p != $parent) {
                    $edits[] = "<span class='ptypetag'>$prop</span>";
                    $parent = $p;
                }
            } else if (startsWith($prop, "source:")) {
                $s = mb_substr($prop, 7);
                if ($s != $source) {
                    $edits[] = "<span class='ptypetag source-history-item'>$prop</span>";
                    $source = $s;
                }
            }
        }
    }
    sort($edits);
    $edits = array_map(function($html) {
        return "<span class='tag-edit'>$html</span>";
    }, $edits);
    $tag_changes = implode(" ", $edits);
    $item['tagChanges'] = $tag_changes;
    $item['date'] = FormatDate($item['Timestamp'], GALLERY_DATE_LONG_FORMAT);
    LoadSingleTableEntry(array(USER_TABLE), "UserId", $item['UserId'], $item['user']);
}
$tag_history_items = array_reverse($tag_history_items);  // Undo reverse order.
?>