<?php
/**
 * RA0 Edition - 이모티콘 AJAX
 *
 * GET /bbs/emoticon_ajax.php       - 전체 이모티콘 목록
 * GET /bbs/emoticon_ajax.php?q=검색어 - 이모티콘 검색
 */

include_once('./_common.php');
include_once(G5_LIB_PATH . '/emoticon.lib.php');

header('Content-Type: application/json; charset=utf-8');

// 파라미터
$keyword = isset($_GET['q']) ? clean_xss_tags($_GET['q']) : '';
$limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50;

// 이모티콘 검색/목록
$emoticons = search_emoticons($keyword, $limit);

// 응답
echo json_encode([
    'emoticons' => $emoticons
], JSON_UNESCAPED_UNICODE);
