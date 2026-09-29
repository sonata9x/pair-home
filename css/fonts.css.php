<?php
include_once('../_common.php');

header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: public, max-age=3600');

// 사용중인 폰트만 직접 가져오기
$config_font_table = G5_TABLE_PREFIX . 'config_font';
$sql = "SELECT fo_import FROM {$config_font_table} WHERE fo_use = 1 ORDER BY fo_order ASC";
$result = sql_query($sql, false);

if($result) {
    while($row = sql_fetch_array($result)) {
        if(!empty($row['fo_import'])) {
            echo $row['fo_import'] . "\n";
        }
    }
}
?>
