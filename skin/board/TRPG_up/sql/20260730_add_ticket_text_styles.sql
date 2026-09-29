ALTER TABLE `ra0_write_trpg_log`
    ADD COLUMN `wr_subject_font` varchar(255) NOT NULL DEFAULT '' AFTER `wr_pc`,
    ADD COLUMN `wr_subject_color` varchar(7) NOT NULL DEFAULT '' AFTER `wr_subject_font`,
    ADD COLUMN `wr_english_title_font` varchar(255) NOT NULL DEFAULT '' AFTER `wr_subject_color`,
    ADD COLUMN `wr_english_title_color` varchar(7) NOT NULL DEFAULT '' AFTER `wr_english_title_font`,
    ADD COLUMN `wr_subtitle_font` varchar(255) NOT NULL DEFAULT '' AFTER `wr_english_title_color`,
    ADD COLUMN `wr_subtitle_color` varchar(7) NOT NULL DEFAULT '' AFTER `wr_subtitle_font`,
    ADD COLUMN `wr_catchphrase_font` varchar(255) NOT NULL DEFAULT '' AFTER `wr_subtitle_color`,
    ADD COLUMN `wr_catchphrase_color` varchar(7) NOT NULL DEFAULT '' AFTER `wr_catchphrase_font`;

UPDATE `ra0_write_trpg_log`
SET `wr_subject_font` = `wr_8`
WHERE `wr_subject_font` = '' AND `wr_8` <> '';
