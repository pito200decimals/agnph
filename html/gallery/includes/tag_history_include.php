<?php
// PHP code that renders a list of tag edits.

// Assumes there are input vars:
// $tag_history_items: sql results.
// $ids: List of ids to keep.

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
$ratingMap = array();
$parentMap = array();
$sourceMap = array();
$tag_history_items = array_reverse($tag_history_items);  // Need to process in reverse order.
$chronological_results = array();
foreach ($tag_history_items as &$item) {
    $pid = $item['PostId'];
    if (!isset($ratingMap[$pid])) $ratingMap[$pid] = "";
    if (!isset($parentMap[$pid])) $parentMap[$pid] = "none";
    if (!isset($sourceMap[$pid])) $sourceMap[$pid] = "";
    $tag_changes = "";
    $adds = array_map(function($tag_id) use ($tags) {
        $typeclass = mb_strtolower($tags[$tag_id]['Type'], "UTF-8")."typetag";
        return "<span class='pscore'>+</span><a href='/gallery/post/?search=".urlencode($tags[$tag_id]['Name'])."'><span class='$typeclass'>".$tags[$tag_id]['Name']."</span></a>";
    }, array_filter(explode(",", $item['TagsAdded']), "mb_strlen"));
    $removes = array_map(function($tag_id) use ($tags) {
        $typeclass = mb_strtolower($tags[$tag_id]['Type'], "UTF-8")."typetag";
        return "<span class='nscore'>-</span><a href='/gallery/post/?search=".urlencode($tags[$tag_id]['Name'])."'><span class='$typeclass'>".$tags[$tag_id]['Name']."</span></a>";
    }, array_filter(explode(",", $item['TagsRemoved']), "mb_strlen"));
    $edits = array_merge($adds, $removes);
    // Add in rating/source/parent changes.
    $propsChanged = $item['PropertiesChanged'];
    if (mb_strlen($propsChanged) > 0) {
        $props = explode(" ", $propsChanged);
        foreach ($props as $prop) {
            if (startsWith($prop, "rating:")) {
                $r = mb_substr($prop, 7);
                if ($r != $ratingMap[$pid]) {
                    $edits[] = "<span class='ptypetag'>$prop</span>";
                    $ratingMap[$pid] = $r;
                }
            } else if (startsWith($prop, "parent:")) {
                $p = mb_substr($prop, 7);
                if ($p != $parentMap[$pid]) {
                    $edits[] = "<span class='ptypetag'>$prop</span>";
                    $parentMap[$pid] = $p;
                }
            } else if (startsWith($prop, "source:")) {
                $s = mb_substr($prop, 7);
                if ($s != $sourceMap[$pid]) {
                    $edits[] = "<span class='ptypetag source-history-item'>$prop</span>";
                    $sourceMap[$pid] = $s;
                }
            }
        }
    }
    sort($edits);
    $edits = array_map(function($html) {
        return "<span class='tag-edit'>$html</span>";
    }, $edits);
    if (!isset($ids) || in_array($item['Id'], $ids)) {
        $tag_changes = implode(" ", $edits);
        $item['tagChanges'] = $tag_changes;
        $item['date'] = FormatDate($item['Timestamp'], GALLERY_DATE_LONG_FORMAT);
        LoadSingleTableEntry(array(USER_TABLE), "UserId", $item['UserId'], $item['user']);
        $chronological_results[] = $item;
    }
}
$tag_history_items = array_reverse($chronological_results);  // Undo reverse order.
?>