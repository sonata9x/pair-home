-- RA0 Edition: g5_board_new 테이블 성능 개선
-- 기존 DB에 복합 인덱스 추가

-- 회원별 시간순 조회 최적화 (마이페이지 등)
ALTER TABLE `g5_board_new`
ADD INDEX `idx_mb_datetime` (`mb_id`, `bn_datetime`);

-- 게시판별 최신글 조회 최적화
ALTER TABLE `g5_board_new`
ADD INDEX `idx_bo_datetime` (`bo_table`, `bn_datetime`);

-- 완료 메시지
SELECT '✅ 복합 인덱스가 성공적으로 추가되었습니다.' AS result;
