<?php
// 상대 경로는 깊은 관리자 경로에서 alert() 경유 호출 시 cwd가 달라져 깨짐 — __DIR__ 기준으로 고정
include_once(__DIR__ . '/../common.php');
?>