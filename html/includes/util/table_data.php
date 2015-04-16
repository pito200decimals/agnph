<?php
// Functions for handling sql table data loading.

// Loads data from a set of tables. Caches results so that multiple lookups for the same ID can be avoided, if possible.
// (simple ID caching, won't handle more complicated queries).
// Returns true on success, false on failure. $tables must be nonempty. Outputs entries to $dest[$id], but does not clear the array.
// If false is returned, the data loaded into $dest is undefined.
// NOTE: Assumes that the IDs are safe to search for.
function LoadTableData($tables, $id_name, $ids, &$dest, $fresh = false) {
    static $cache = array();
    if (sizeof($tables) == 0) return false;
    if (sizeof($ids) == 0) return true;
    sort($tables);
    sort($ids);
    $table_key = implode("|", $tables);
    $ids_to_load = array();
    if (!$fresh && isset($cache[$table_key])) {
        $table_cache = &$cache[$table_key];
        foreach ($ids as $id) {
            if (isset($cache[$table_key][$id])) {
                $dest[$id] = $cache[$table_key][$id];
            } else {
                $ids_to_load[] = $id;
            }
        }
    } else {
        $cache[$table_key] = array();
        $ids_to_load = $ids;
    }
    if (sizeof($ids_to_load) > 0) {
        $ids_to_load = array_unique($ids_to_load);
        $sql = "SELECT * FROM ";
        $first_table = array_values($tables)[0];
        $tables = array_slice($tables, 1);
        $table_strings = array_map(function($table) use ($first_table, $id_name) {
                return " JOIN $table ON $first_table.$id_name=$table.$id_name";
            }, $tables);
        $sql .= $first_table.implode("", $table_strings);
        $joined_ids = implode(",", $ids_to_load);
        $sql .= " WHERE $first_table.$id_name IN ($joined_ids);";
        if (sql_query_into($result, $sql, 0)) {
            while ($row = $result->fetch_assoc()) {
                $dest[$row[$id_name]] = $row;
                $cache[$table_key][$row[$id_name]] = $row;
            }
            return true;
        } else {
            return false;
        }
    } else {
        // No ids to query.
        return true;
    }
}

// Same as above, but loads only a single entry. Returns false if the id was not found.
function LoadSingleTableEntry($tables, $id_name, $id, &$dest, $fresh = false) {
    $ret = array();
    $success = LoadTableData($tables, $id_name, array($id), $ret, $fresh);
    if (!$success) return false;
    // Id was not found.
    if (!isset($ret[$id])) return false;
    $dest = $ret[$id];
    return true;
}

// Loads all user settings. Should be for the currently logged-in user. Called from the authentication header code.
// Code should be updated to include all user data tables.
function LoadAllUserPreferences($uid, &$user, $fresh = false) {
    $table_list = array(USER_TABLE, FORUMS_USER_PREF_TABLE, GALLERY_USER_PREF_TABLE, FICS_USER_PREF_TABLE);
    return LoadSingleTableEntry($table_list, "UserId", $uid, $user, $fresh);
}
?>