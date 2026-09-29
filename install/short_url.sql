-- RA0 Edition - URL 단축 시스템
-- 테이블 생성 스크립트

CREATE TABLE IF NOT EXISTS `ra0_short_url` (
  `su_id` int(11) NOT NULL AUTO_INCREMENT,
  `su_key` varchar(10) NOT NULL COMMENT '짧은 URL 키 (예: abc123)',
  `su_url` text NOT NULL COMMENT '원본 URL',
  `su_bo_table` varchar(20) DEFAULT NULL COMMENT '게시판 테이블명',
  `su_wr_id` int(11) DEFAULT NULL COMMENT '게시글 ID',
  `su_datetime` datetime NOT NULL COMMENT '생성일시',
  PRIMARY KEY (`su_id`),
  UNIQUE KEY `su_key` (`su_key`),
  KEY `su_bo_table` (`su_bo_table`,`su_wr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='URL 단축 서비스';
