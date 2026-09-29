-- RA0 Edition 이웃 피드 시스템 테이블
-- 설치: 관리자 > 게시판설정 > 이웃 관리에서 자동 설치

-- g5_board 테이블에 피드 노출 여부 컬럼 추가
-- ALTER TABLE g5_board ADD bo_feed_use TINYINT(1) NOT NULL DEFAULT '1' COMMENT '피드 노출 여부' AFTER bo_use_cert;

-- 이웃 사이트 테이블
CREATE TABLE IF NOT EXISTS `g5_feed_neighbor` (
  `fn_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '고유 ID',
  `fn_site_name` varchar(255) NOT NULL DEFAULT '' COMMENT '사이트명',
  `fn_site_url` varchar(500) NOT NULL DEFAULT '' COMMENT '사이트 URL',
  `fn_feed_url` varchar(500) NOT NULL DEFAULT '' COMMENT '피드 API URL',
  `fn_created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
  `fn_updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  PRIMARY KEY (`fn_id`),
  UNIQUE KEY `fn_site_url` (`fn_site_url`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='이웃 사이트 목록';
