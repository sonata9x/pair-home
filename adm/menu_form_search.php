<?php
require_once './_common.php';

if (!$is_admin) {
    die('관리자만 접근 가능합니다.');
}

$type = isset($_REQUEST['type']) ? preg_replace('/[^0-9a-z_]/i', '', $_REQUEST['type']) : '';

switch ($type) {
    case 'group':
        // 그룹 시스템 제거됨 - 그룹 케이스 무력화
        $sql = '';
        break;
    case 'community':
        // 자캐 커뮤니티 시스템 메뉴 — 페이지 파일 존재하는 항목만 노출 (확장팩 미설치는 자동 제외)
        $__community_pages_all = array(
            array('id' => 'character_list', 'subject' => '멤버', 'link' => 'community/character/character_list.php'),
            array('id' => 'shop', 'subject' => '상점', 'link' => 'community/shop.php'),
            array('id' => 'field', 'subject' => '필드', 'link' => 'community/field/index.php'),
            array('id' => 'dungeon', 'subject' => '던전', 'link' => 'community/dungeon/index.php'),
            array('id' => 'raid', 'subject' => '레이드', 'link' => 'community/raid/index.php'),
            array('id' => 'tile', 'subject' => '탐험보드', 'link' => 'community/tile/index.php'),
            array('id' => 'tilemap', 'subject' => '모험', 'link' => 'community/tilemap/play.php'),
            array('id' => 'square', 'subject' => '스퀘어', 'link' => 'community/lobby/square/index.php'),
            array('id' => 'gift_history', 'subject' => '선물 내역', 'link' => 'community/gift_history.php'),
            array('id' => 'trade', 'subject' => '교환', 'link' => 'community/trade.php'),
            array('id' => 'transfer', 'subject' => '송금', 'link' => 'community/transfer.php'),
            array('id' => 'character_create', 'subject' => '캐릭터 생성', 'link' => 'community/character/character_create.php'),
            array('id' => 'character', 'subject' => '내 캐릭터', 'link' => 'community/character/character.php'),
            array('id' => 'quest', 'subject' => '퀘스트', 'link' => 'community/quest.php'),
            array('id' => 'codex', 'subject' => '도감', 'link' => 'community/codex/index.php'),
            array('id' => 'myroom', 'subject' => '마이룸', 'link' => 'community/myroom/index.php'),
            array('id' => 'attend', 'subject' => '출석', 'link' => 'community/attend/index.php'),
            array('id' => 'salary', 'subject' => '급여', 'link' => 'community/salary/index.php'),
            array('id' => 'gacha', 'subject' => '뽑기', 'link' => 'community/gacha/index.php'),
            array('id' => 'auction', 'subject' => '경매장', 'link' => 'community/auction/index.php'),
            array('id' => 'exchange', 'subject' => '거래소', 'link' => 'community/exchange/index.php'),
            array('id' => 'betting', 'subject' => '내기', 'link' => 'community/betting/index.php')
        );
        $community_pages = array();
        foreach ($__community_pages_all as $__cp) {
            $__path = strtok($__cp['link'], '?'); // 쿼리스트링 제거 후 파일 체크
            if (file_exists(G5_PATH . '/' . $__path)) {
                $community_pages[] = $__cp;
            }
        }
        // 모험 페이지 카테고리 동적 추가 — 로비팩 설치 + 카테고리 정의된 경우에만
        if (function_exists('lobby_get_adventure_categories')) {
            foreach (lobby_get_adventure_categories() as $__ac) {
                $community_pages[] = array(
                    'id'      => 'adventure_' . $__ac['code'],
                    'subject' => '모험 페이지 — ' . $__ac['label'],
                    'link'    => 'community/lobby/adventure.php?code=' . urlencode($__ac['code']),
                );
            }
        }
        break;
    case 'game':
        // 게임 확장팩 메뉴 (해당 게임 파일 존재 시)
        $game_pages = array();
        if (file_exists(G5_PATH . '/game/roulette.php')) {
            $game_pages[] = array('id' => 'roulette', 'subject' => '룰렛', 'link' => 'game/roulette.php');
        }
        break;
    case 'kit':
        // 키트 확장팩 메뉴 (설치된 키트의 사용자 화면만 노출)
        $kit_pages = array();
        if (file_exists(G5_PATH . '/extend/re_config.php') && file_exists(G5_PATH . '/re/index.php')) {
            $kit_pages[] = array('id' => 're_rooms', 'subject' => '관계 갈래', 'link' => 're/');
        }
        break;
    case 'store':
        // 쇼핑몰 시스템 메뉴
        $store_pages = array(
            array('id' => 'mypage', 'subject' => '마이페이지'),
            array('id' => 'cart', 'subject' => '장바구니'),
            array('id' => 'orders', 'subject' => '주문 내역'),
            array('id' => 'my_coupons', 'subject' => '내 쿠폰함'),
            array('id' => 'coupon_register', 'subject' => '쿠폰 등록'),
            array('id' => 'charge', 'subject' => '포인트 충전'),
        );
        break;    
    case 'board':
        // 그룹 시스템 제거됨 - gr_id 제거
        $sql = " select bo_table as id, bo_subject as subject
                    from {$g5['board_table']}
                    order by bo_order, bo_table ";
        break;
    case 'content':
        // 페이지(내용) 목록
        $sql = " select co_id as id, co_subject as subject
                    from {$g5['content_table']}
                    order by co_id ";
        break;
    default:
        $sql = '';
        break;
}

// 커뮤니티 타입 처리
if ($type == 'community') {
    if (empty($community_pages)) {
        echo '<p style="padding:20px; text-align:center; color:#666;">커뮤니티 확장팩이 설치되지 않았습니다.</p>';
    } else {
    for ($i = 0; $i < count($community_pages); $i++) {
        $row = $community_pages[$i];
        if ($i == 0) {
            $bbs_subject_title = '커뮤니티 페이지';
            ?>

<div class="tbl_head01 tbl_wrap">
    <table>
        <thead>
            <tr>
                <th scope="col"><?php echo $bbs_subject_title; ?></th>
                <th scope="col" style="width:120px;">메뉴 추가</th>
            </tr>
        </thead>
        <tbody>
            <?php
        }
        ?>
        <tr>
            <td class="td_left">
                <strong><?php echo $row['subject']; ?></strong>
                <br><small style="color:#666;"><?php echo $row['link']; ?></small>
            </td>
            <td class="td_mng">
                <input type="hidden" name="subject[]" value="<?php echo preg_replace('/[\'\"]/', '', $row['subject']); ?>">
                <input type="hidden" name="link[]" value="<?php echo G5_URL; ?>/<?php echo $row['link']; ?>">
                <button type="button" class="add_select btn btn_03">
                    <span class="sound_only"><?php echo $row['subject']; ?> </span>메뉴 추가
                </button>
            </td>
        </tr>
        <?php
    }
    ?>
        </tbody>
    </table>
</div>
    <?php
    } // end else (empty community_pages)
} elseif ($type == 'game') {
    // 게임 타입 처리
    if (empty($game_pages)) {
        echo '<p style="padding:20px; text-align:center; color:#666;">게임 확장팩이 설치되지 않았습니다.</p>';
    } else {
        for ($i = 0; $i < count($game_pages); $i++) {
            $row = $game_pages[$i];
            if ($i == 0) {
                $bbs_subject_title = '게임 페이지';
                ?>

<div class="tbl_head01 tbl_wrap">
    <table>
        <thead>
            <tr>
                <th scope="col"><?php echo $bbs_subject_title; ?></th>
                <th scope="col" style="width:120px;">메뉴 추가</th>
            </tr>
        </thead>
        <tbody>
            <?php
            }
            ?>
        <tr>
            <td class="td_left">
                <strong><?php echo $row['subject']; ?></strong>
                <br><small style="color:#666;"><?php echo $row['link']; ?></small>
            </td>
            <td class="td_mng">
                <input type="hidden" name="subject[]" value="<?php echo preg_replace('/[\'\"]/', '', $row['subject']); ?>">
                <input type="hidden" name="link[]" value="<?php echo G5_URL; ?>/<?php echo $row['link']; ?>">
                <button type="button" class="add_select btn btn_03">
                    <span class="sound_only"><?php echo $row['subject']; ?> </span>메뉴 추가
                </button>
            </td>
        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
    <?php
    }
} elseif ($type == 'kit') {
    // 키트 타입 처리
    if (empty($kit_pages)) {
        echo '<p style="padding:20px; text-align:center; color:#666;">사용할 수 있는 키트 화면이 없습니다.</p>';
    } else {
        for ($i = 0; $i < count($kit_pages); $i++) {
            $row = $kit_pages[$i];
            if ($i == 0) {
                $bbs_subject_title = '키트 페이지';
                ?>

<div class="tbl_head01 tbl_wrap">
    <table>
        <thead>
            <tr>
                <th scope="col"><?php echo $bbs_subject_title; ?></th>
                <th scope="col" style="width:120px;">메뉴 추가</th>
            </tr>
        </thead>
        <tbody>
            <?php
            }
            ?>
        <tr>
            <td class="td_left">
                <strong><?php echo $row['subject']; ?></strong>
                <br><small style="color:#666;"><?php echo $row['link']; ?></small>
            </td>
            <td class="td_mng">
                <input type="hidden" name="subject[]" value="<?php echo preg_replace('/[\'\"]/', '', $row['subject']); ?>">
                <input type="hidden" name="link[]" value="<?php echo G5_URL; ?>/<?php echo $row['link']; ?>">
                <button type="button" class="add_select btn btn_03">
                    <span class="sound_only"><?php echo $row['subject']; ?> </span>메뉴 추가
                </button>
            </td>
        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
    <?php
    }
} elseif ($type == 'store') {
    // 스토어 타입 처리
    for ($i = 0; $i < count($store_pages); $i++) {
        $row = $store_pages[$i];
        if ($i == 0) {
            $bbs_subject_title = '스토어 페이지';
            ?>

<div class="tbl_head01 tbl_wrap">
    <table>
        <thead>
            <tr>
                <th scope="col"><?php echo $bbs_subject_title; ?></th>
                <th scope="col" style="width:120px;">메뉴 추가</th>
            </tr>
        </thead>
        <tbody>
            <?php
        }
        ?>
        <tr>
            <td class="td_left">
                <strong><?php echo $row['subject']; ?></strong>
                <br><small style="color:#666;">store/<?php echo $row['id']; ?>.php</small>
            </td>
            <td class="td_mng">
                <input type="hidden" name="subject[]" value="<?php echo preg_replace('/[\'\"]/', '', $row['subject']); ?>">
                <input type="hidden" name="link[]" value="<?php echo G5_URL; ?>/store/<?php echo $row['id']; ?>.php">
                <button type="button" class="add_select btn btn_03">
                    <span class="sound_only"><?php echo $row['subject']; ?> </span>메뉴 추가
                </button>
            </td>
        </tr>
        <?php
    }
    ?>
        </tbody>
    </table>
</div>
    <?php
} elseif ($sql) {
    $result = sql_query($sql);

    for ($i = 0; $row = sql_fetch_array($result); $i++) {
        if ($i == 0) {
            $bbs_subject_title = ($type == 'board') ? '게시판제목' : '제목';
            ?>

<div class="tbl_head01 tbl_wrap">
    <table>
        <thead>
            <tr>
                <th scope="col"><?php echo $bbs_subject_title; ?></th>
                <?php // 그룹 시스템 제거됨 - 게시판 그룹 컴럼 제거
                // if ($type == 'board') { ?>
                    <!-- <th scope="col">게시판 그룹</th> -->
                <?php // } ?>
                <th scope="col">선택</th>
            </tr>
        </thead>
        <tbody>

        <?php }
        switch ($type) {
            case 'group':
                // 그룹 시스템 제거됨 - 그룹 링크 제거
                $link = '';
                break;
            case 'board':
                $link = get_pretty_url($row['id']);
                break;
            case 'content':
                $link = get_pretty_url('content', $row['id']);
                break;
            default:
                $link = '';
                break;
        }
        ?>

        <tr>
            <td><?php echo $row['subject']; ?></td>
            <?php
            // 그룹 시스템 제거됨 - 그룹 정보 표시 제거
            /*
            if ($type == 'board') {
                $group = get_call_func_cache('get_group', array($row['gr_id']));
            ?>
                <td><?php echo $group['gr_subject']; ?></td>
            <?php } */
            ?>
            <td class="td_mngsmall">
                <input type="hidden" name="subject[]" value="<?php echo preg_replace('/[\'\"]/', '', $row['subject']); ?>">
                <input type="hidden" name="link[]" value="<?php echo $link; ?>">
                <button type="button" class="add_select btn btn_03"><span class="sound_only"><?php echo $row['subject']; ?> </span>선택</button>
            </td>
        </tr>

    <?php } ?>

        </tbody>
    </table>
</div>

<div class="local_desc01 menu_exists_tip" style="display:none">
    <p>* <strong>빨간색</strong>의 제목은 이미 메뉴에 연결되어 경우 표시됩니다.</p>
</div>

<div class="btn_win02 btn_win">
    <button type="button" class="btn_02 btn" onclick="window.close();">창닫기</button>
</div>

<?php } else { ?>
<div class="tbl_frm01 tbl_wrap">
    <table>
        <colgroup>
            <col class="grid_2">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <th scope="row"><label for="me_name">메뉴<strong class="sound_only"> 필수</strong></label></th>
                <td><input type="text" name="me_name" id="me_name" required class="frm_input required"></td>
            </tr>
            <tr>
                <th scope="row"><label for="me_link">링크<strong class="sound_only"> 필수</strong></label></th>
                <td>
                    <?php echo help('링크는 http://를 포함해서 입력해 주세요.'); ?>
                    <input type="text" name="me_link" id="me_link" required class="frm_input full_input required">
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="btn_win02 btn_win">
    <button type="button" id="add_manual" class="btn_submit btn">추가</button>
    <button type="button" class="btn_02 btn" onclick="window.close();">창닫기</button>
</div>
<?php } // end if;
