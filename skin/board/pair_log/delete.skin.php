<?php
if (!defined('_GNUBOARD_')) exit;
include_once G5_LIB_PATH.'/pair_board.lib.php';
$table = pair_log_track_table();
sql_query("DELETE FROM {$table} WHERE bo_table='".sql_real_escape_string($bo_table)."' AND wr_id='".(int)$write['wr_id']."'", false);
