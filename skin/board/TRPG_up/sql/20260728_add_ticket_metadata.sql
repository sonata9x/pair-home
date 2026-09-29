-- TRPG_up 티켓 목록용 메타데이터 컬럼
-- 현재 운영 중인 로그 게시판 테이블에 한 번만 실행합니다.
ALTER TABLE `ra0_write_trpg_log`
    ADD COLUMN `wr_english_title` varchar(255) NOT NULL DEFAULT '' AFTER `wr_title`,
    ADD COLUMN `wr_catchphrase` varchar(500) NOT NULL DEFAULT '' AFTER `wr_english_title`,
    ADD COLUMN `wr_playtime` varchar(50) NOT NULL DEFAULT '' AFTER `wr_catchphrase`,
    ADD COLUMN `wr_kpc` varchar(255) NOT NULL DEFAULT '' AFTER `wr_playtime`,
    ADD COLUMN `wr_pc` varchar(255) NOT NULL DEFAULT '' AFTER `wr_kpc`;
