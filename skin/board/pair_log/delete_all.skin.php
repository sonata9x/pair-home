<?php
if (!defined('_GNUBOARD_')) exit;
include_once G5_LIB_PATH.'/pair_board.lib.php';
$ids = array_values(array_filter(array_map('intval', (array)$tmp_array)));
if ($ids) {
    $table = pair_log_track_table();
    sql_query("DELETE FROM {$table} WHERE bo_table='".sql_real_escape_string($bo_table)."' AND wr_id IN (".implode(',', $ids).")", false);
}
