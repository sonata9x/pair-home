<?php
include_once('./_common.php');

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}

$bo_table = isset($_GET['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['bo_table']) : 'guest';
$write_table = $g5['write_prefix'] . $bo_table;

echo "<h2>게시판 테이블 진단: {$bo_table}</h2>";
echo "<hr>";

// 1. g5_board 테이블에 게시판 정보가 있는지 확인
echo "<h3>1. 게시판 정보 확인 (g5_board 테이블)</h3>";
$board = get_board_db($bo_table, true);
if ($board) {
    echo "<p style='color:green'>✓ 게시판 정보 존재: {$board['bo_subject']}</p>";
} else {
    echo "<p style='color:red'>✗ 게시판 정보 없음</p>";
}
echo "<hr>";

// 2. write 테이블이 존재하는지 확인
echo "<h3>2. Write 테이블 존재 확인 ({$write_table})</h3>";
$table_exists = sql_query("SHOW TABLES LIKE '{$write_table}'", false);
$table_row = sql_fetch_array($table_exists);
if ($table_row) {
    echo "<p style='color:green'>✓ 테이블 존재함</p>";

    // 테이블 구조 확인
    echo "<h4>테이블 구조:</h4>";
    $columns = sql_query("SHOW COLUMNS FROM {$write_table}");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>필드명</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($col = sql_fetch_array($columns)) {
        $highlight = ($col['Field'] == 'wr_secret') ? " style='background-color: yellow;'" : "";
        echo "<tr{$highlight}>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";

} else {
    echo "<p style='color:red'>✗ 테이블 없음!</p>";
    echo "<p><strong>해결 방법:</strong></p>";
    echo "<ol>";
    echo "<li><a href='board_table_create.php?bo_table={$bo_table}'>테이블 자동 생성하기</a></li>";
    echo "<li>또는 아래 SQL을 직접 실행하세요</li>";
    echo "</ol>";

    // sql_write.sql 읽어서 CREATE TABLE 문 생성
    $sql_file = file('./sql_write.sql');
    if ($sql_file) {
        $sql_file = get_db_create_replace($sql_file);
        $sql = implode("\n", $sql_file);
        $sql = str_replace('__TABLE_NAME__', $write_table, $sql);
        $sql = str_replace(';', '', $sql);

        echo "<h4>실행할 SQL:</h4>";
        echo "<textarea style='width:100%; height:300px;'>" . htmlspecialchars($sql) . "</textarea>";
    }
}
echo "<hr>";

// 3. MySQL 버전 확인
echo "<h3>3. MySQL 정보</h3>";
$mysql_version = sql_fetch("SELECT VERSION() as version");
if ($mysql_version) {
    echo "<p>MySQL 버전: " . $mysql_version['version'] . "</p>";
}

?>
