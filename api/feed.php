<?php
/**
 * RA0 Edition 피드 API
 *
 * 다른 라공 에디션 사이트에서 이 사이트의 새 글을 가져갈 수 있도록
 * JSON 형식으로 피드를 제공합니다.
 *
 * @package RA0Edition
 * @since 1.0
 *
 * 사용법:
 * GET /api/feed.php
 * GET /api/feed.php?limit=20
 *
 * 응답 형식:
 * {
 *   "site_name": "사이트명",
 *   "site_url": "https://example.com",
 *   "updated": "2024-12-21T15:30:00+09:00",
 *   "items": [
 *     {
 *       "id": "글ID",
 *       "board": "게시판명",
 *       "title": "제목",
 *       "summary": "요약",
 *       "author": "작성자",
 *       "thumbnail": "썸네일URL",
 *       "date": "2024-12-21T15:00:00+09:00",
 *       "link": "글 URL"
 *     }
 *   ]
 * }
 */

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Content-Type: application/json; charset=utf-8');

// OPTIONS 요청 처리 (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// GET 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method Not Allowed'));
    exit;
}

// 그누보드 공통 파일 로드
$_POST['url'] = 'api/feed.php';
include_once('../common.php');

// 피드 라이브러리 로드
include_once(G5_LIB_PATH . '/feed.lib.php');

// 파라미터
$limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 20;
if ($limit < 1) $limit = 20;

// 피드 아이템 조회
$items = get_feed_items($limit);

// 응답 생성
$response = array(
    'site_name' => $config['cf_title'],
    'site_url' => G5_URL,
    'updated' => date('c'),
    'items' => $items
);

// JSON 출력
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
