<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('ra0_board_coadmin_config')) {
    function ra0_board_coadmin_config() {
        global $board_skin_path;

        if (empty($board_skin_path)) return array();

        $file = $board_skin_path . '/coadmin.permission.php';
        if (!is_file($file)) return array();

        $config = include $file;
        return is_array($config) ? $config : array();
    }
}

if (!function_exists('ra0_board_coadmin_current_write')) {
    function ra0_board_coadmin_current_write() {
        global $g5, $bo_table, $wr_id, $write_table, $write, $wr;

        if (isset($wr) && is_array($wr) && !empty($wr['wr_id'])) {
            return $wr;
        }

        if (isset($write) && is_array($write) && !empty($write['wr_id'])) {
            return $write;
        }

        if (empty($bo_table) || empty($wr_id)) return array();

        $wt = !empty($write_table) ? $write_table : $g5['write_prefix'] . preg_replace('/[^a-z0-9_]/i', '', $bo_table);
        $row = sql_fetch("SELECT * FROM {$wt} WHERE wr_id = '" . (int) $wr_id . "'", false);

        return is_array($row) ? $row : array();
    }
}

if (!function_exists('ra0_board_coadmin_collect_ids')) {
    function ra0_board_coadmin_collect_ids($raw) {
        $ids = array();

        if (is_array($raw)) {
            $is_assoc = array_keys($raw) !== range(0, count($raw) - 1);
            if ($is_assoc) {
                foreach (array('mb_id', 'id', 'member_id') as $key) {
                    if (!empty($raw[$key])) {
                        $ids[] = $raw[$key];
                    }
                }
            } else {
                foreach ($raw as $item) {
                    if (is_array($item)) {
                        $ids = array_merge($ids, ra0_board_coadmin_collect_ids($item));
                    } else {
                        $ids[] = $item;
                    }
                }
            }
        } else {
            $raw = html_entity_decode(stripslashes((string) $raw), ENT_QUOTES, 'UTF-8');
            if (trim($raw) === '') return array();

            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $ids = array_merge($ids, ra0_board_coadmin_collect_ids($decoded));
            } else {
                $ids = preg_split('/[,;\s]+/', $raw);
            }
        }

        $clean = array();
        foreach ($ids as $id) {
            $id = preg_replace('/[^a-zA-Z0-9_@.\-]/', '', trim((string) $id));
            if ($id !== '') $clean[$id] = $id;
        }

        return array_values($clean);
    }
}

if (!function_exists('ra0_board_coadmin_matches')) {
    function ra0_board_coadmin_matches($row, $member, $config) {
        $mb_id = trim((string) ($member['mb_id'] ?? ''));
        if ($mb_id === '' || empty($row['wr_id'])) return false;
        if (!empty($row['mb_id']) && $row['mb_id'] === $mb_id) return false;

        $fields = !empty($config['fields']) && is_array($config['fields'])
            ? $config['fields']
            : array('wr_coadmin', 'wr_link2', 'wr_3');

        foreach ($fields as $field) {
            if (!array_key_exists($field, $row)) continue;
            if (in_array($mb_id, ra0_board_coadmin_collect_ids($row[$field]), true)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('ra0_board_coadmin_grant')) {
    function ra0_board_coadmin_grant($context = '') {
        global $board, $member, $is_admin, $notice, $wr_id, $ra0_board_coadmin_granted;

        if ($is_admin) return;

        $is_admin = 'coadmin';
        $ra0_board_coadmin_granted = true;

        $level_keys = array('bo_write_level', 'bo_reply_level', 'bo_upload_level', 'bo_html_level', 'bo_link_level');
        foreach ($level_keys as $key) {
            if (isset($board[$key])) {
                $member['mb_level'] = max((int) ($member['mb_level'] ?? 0), (int) $board[$key]);
            }
        }

        if ($context === 'write_update') {
            $notice_ids = array_filter(array_map('intval', explode(',', $board['bo_notice'] ?? '')));
            $notice = in_array((int) $wr_id, $notice_ids, true) ? 1 : '';
            unset($_POST['notice'], $_REQUEST['notice']);
        }
    }
}

if (!function_exists('ra0_board_coadmin_grant_read')) {
    function ra0_board_coadmin_grant_read($row) {
        global $board, $bo_table, $member, $ra0_board_coadmin_read_granted;

        $ra0_board_coadmin_read_granted = true;

        if (isset($board['bo_read_level'])) {
            $member['mb_level'] = max((int) ($member['mb_level'] ?? 0), (int) $board['bo_read_level']);
        }

        if (!empty($row['wr_num']) && !empty($row['wr_option']) && strpos($row['wr_option'], 'secret') !== false) {
            set_session('ss_secret_' . $bo_table . '_' . $row['wr_num'], true);
        }
    }
}

if (!function_exists('ra0_board_apply_coadmin_read_permission')) {
    function ra0_board_apply_coadmin_read_permission() {
        global $bo_table, $member, $is_admin;

        if (empty($bo_table) || empty($member['mb_id']) || $is_admin) return;

        $config = ra0_board_coadmin_config();
        if (empty($config)) return;

        $row = ra0_board_coadmin_current_write();
        if (ra0_board_coadmin_matches($row, $member, $config)) {
            ra0_board_coadmin_grant_read($row);
        }
    }
}

if (!function_exists('ra0_board_apply_coadmin_permission')) {
    function ra0_board_apply_coadmin_permission($w, $context = '') {
        global $member;

        if ($w !== 'u' || empty($member['mb_id'])) return;

        $config = ra0_board_coadmin_config();
        if (empty($config)) return;

        $row = ra0_board_coadmin_current_write();
        if (ra0_board_coadmin_matches($row, $member, $config)) {
            ra0_board_coadmin_grant($context);
        }
    }
}

if (!function_exists('ra0_board_coadmin_on_write')) {
    function ra0_board_coadmin_on_write($board, $wr_id, $w) {
        ra0_board_apply_coadmin_permission($w, 'write');
    }
}

if (!function_exists('ra0_board_coadmin_on_write_update')) {
    function ra0_board_coadmin_on_write_update($board, $wr_id, $w, $qstr) {
        ra0_board_apply_coadmin_permission($w, 'write_update');
    }
}

add_event('bbs_write', 'ra0_board_coadmin_on_write', 1, 3);
add_event('write_update_before', 'ra0_board_coadmin_on_write_update', 1, 4);

ra0_board_apply_coadmin_read_permission();
