<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

// 캐릭터 존재 확인 (로그인한 회원이고 커뮤니티 확장팩이 설치된 경우만)
if ($is_member && is_community_installed()) {
    $user_character = get_character($member['mb_id']);
    if (!$user_character) {
        alert('타임라인에 글을 쓰려면 먼저 캐릭터를 생성해주세요.', G5_URL.'/community/character/character_create.php');
    }
}

// 타임라인 답글 작성인 경우
// 새 글 작성인지 답글 작성인지 명확히 구분
if ($w == '' && !isset($_REQUEST['tm_parent'])) {
    // 새 글 작성 - tm_parent는 0
    $tm_parent = 0;
} else if (isset($_REQUEST['tm_parent']) && $_REQUEST['tm_parent']) {
    // URL에 tm_parent가 명시적으로 전달된 경우 (답글)
    $tm_parent = (int)$_REQUEST['tm_parent'];
} else if ($w == 'u' && isset($write['tm_parent'])) {
    // 수정 모드일 때는 기존 글의 tm_parent 유지
    $tm_parent = $write['tm_parent'];
} else {
    $tm_parent = 0;
}
$parent_subject = '';

if ($tm_parent) {
    $parent = sql_fetch(" SELECT * FROM {$write_table} WHERE wr_id = '{$tm_parent}' ");
    if ($parent['wr_id']) {
        $parent_subject = $parent['wr_subject'];
    }
}

// 레시피 목록 가져오기 (답글이 아닌 경우에만, Community Extension 설치 시에만)
$available_recipes = array();
$user_inventory = array();
if (!$tm_parent && $is_member && is_community_installed() && defined('G5_COMMUNITY_PATH')) {
    // recipe.lib.php 포함
    if (file_exists(G5_COMMUNITY_PATH.'/lib/recipe.lib.php')) {
        include_once(G5_COMMUNITY_PATH.'/lib/recipe.lib.php');
        // 인벤토리에 보유한 레시피만 가져오기
        if (function_exists('get_inventory_recipes')) {
            $recipe_result = get_inventory_recipes($member['mb_id']);
            while ($recipe = sql_fetch_array($recipe_result, MYSQLI_ASSOC)) {
                // 재료 파싱
                $materials = json_decode($recipe['rc_materials'], true);
                $recipe['parsed_materials'] = $materials;
                
                // 재료 보유 여부 체크
                $has_all_materials = true;
                $material_status = array();
                
                if (is_array($materials)) {
                    foreach ($materials as $material) {
                        // 키 이름 확인 (it_id 또는 item_id)
                        $item_id = isset($material['it_id']) ? $material['it_id'] : (isset($material['item_id']) ? $material['item_id'] : 0);
                        $required_qty = isset($material['quantity']) ? $material['quantity'] : 1;
                    
                    // 사용자 인벤토리에서 해당 아이템 수량 조회
                    $inv_sql = "SELECT SUM(inv_quantity) as total_qty 
                               FROM ".G5_TABLE_PREFIX."community_inventory 
                               WHERE mb_id = '{$member['mb_id']}' AND it_id = {$item_id}";
                    $inv_result = sql_fetch($inv_sql);
                    $owned_qty = $inv_result['total_qty'] ?? 0;
                    
                    // 아이템 이름 가져오기
                    $item_name = '';
                    if ($item_id > 0) {
                        $item_sql = "SELECT it_name FROM ".G5_TABLE_PREFIX."community_item WHERE it_id = {$item_id}";
                        $item_result = sql_fetch($item_sql);
                        $item_name = $item_result['it_name'] ?? '';
                    }
                    
                    $material_status[] = array(
                        'it_id' => $item_id,
                        'it_name' => $item_name,
                        'required' => $required_qty,
                        'have' => $owned_qty,
                        'sufficient' => ($owned_qty >= $required_qty)
                    );
                    
                        if ($owned_qty < $required_qty) {
                            $has_all_materials = false;
                        }
                    }
                }
                
                $recipe['has_materials'] = $has_all_materials;
                $recipe['material_status'] = $material_status;
                $available_recipes[] = $recipe;
            }
        }
    }
}
?>

<section id="bo_w" class="timeline-write">
    <!-- 게시물 작성/수정 시작 { -->
    <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="tm_parent" value="<?php echo $tm_parent ?>">
    
    <?php
    $option = '';
    $option_hidden = '';
    if ($is_notice || $is_html || $is_secret) {
        $option = '';
        if ($is_notice) {
            $option .= "\n".'<input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'>'."\n".'<label for="notice">공지</label>';
        }

        if ($is_html) {
            if ($is_dhtml_editor) {
                $option_hidden .= '<input type="hidden" value="html1" name="html">';
            } else {
                $option .= "\n".'<input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'<label for="html">HTML</label>';
            }
        }

        if ($is_secret) {
            if ($is_admin || $is_secret==1) {
                $option .= "\n".'<input type="checkbox" id="secret" name="secret" value="secret" '.$secret_checked.'>'."\n".'<label for="secret">비밀글</label>';
            } else {
                $option_hidden .= '<input type="hidden" name="secret" value="secret">';
            }
        }
    }

    echo $option_hidden;
    ?>

    <?php if ($is_category) { ?>
    <div class="bo_w_select write_div">
        <label for="ca_name" class="sound_only">분류<strong>필수</strong></label>
        <select name="ca_name" id="ca_name" required>
            <option value="">분류를 선택하세요</option>
            <?php echo $category_option ?>
        </select>
    </div>
    <?php } ?>

    <div class="bo_w_info write_div">
    <?php if ($is_name) { ?>
        <label for="wr_name" class="sound_only">이름<strong>필수</strong></label>
        <input type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required class="frm_input required" placeholder="이름">
    <?php } ?>

    <?php if ($is_password) { ?>
        <label for="wr_password" class="sound_only">비밀번호<strong>필수</strong></label>
        <input type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> class="frm_input <?php echo $password_required ?>" placeholder="비밀번호">
    <?php } ?>

    <?php /* 이메일 필드 제거 (2025-10-27)
    if ($is_email) { ?>
        <label for="wr_email" class="sound_only">이메일</label>
        <input type="email" name="wr_email" value="<?php echo $email ?>" id="wr_email" class="frm_input email " placeholder="이메일">
    <?php } */ ?>
    </div>

    <?php if ($option) { ?>
    <div class="write_div">
        <span class="sound_only">옵션</span>
        <?php echo $option ?>
    </div>
    <?php } ?>

    <?php if ($tm_parent && $parent_subject) { ?>
    <div class="write_div timeline-parent">
        <strong>답글 대상:</strong> <?php echo $parent_subject ?>
    </div>
    <?php } ?>

    <?php if (!$tm_parent && !empty($available_recipes)) { ?>
    
    <!-- 레시피 선택 영역 (새 글 작성시에만 표시) - 복수 선택 가능 -->
    <div class="write_div recipe-select-area">
        <label><i class="fa fa-flask"></i> 조합 레시피 (복수 선택 가능)</label>
        
        <div id="recipe-container">
            <!-- 동적으로 레시피 아이템이 추가될 영역 -->
        </div>
        
        <button type="button" id="add-recipe-btn" class="btn btn-sm" style="margin-top: 10px;">
            <i class="fa fa-plus"></i> 레시피 추가
        </button>
        
        <!-- 레시피 JSON 데이터 저장용 hidden field -->
        <input type="hidden" name="recipes_json" id="recipes_json" value="">
        
        <!-- 전체 요약 정보 표시 영역 -->
        <div id="recipe-summary" class="recipe-details" style="display:none; margin-top: 15px;">
            <div class="recipe-info">
                <h4>조합 요약</h4>
                <div id="recipe-summary-content"></div>
                <div class="recipe-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>주의:</strong> 글 작성과 함께 모든 조합이 순차적으로 실행됩니다.
                    실패 시에도 재료는 반환되지 않습니다.
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // 레시피 데이터 및 인벤토리 정보
    var availableRecipes = <?php echo json_encode($available_recipes); ?>;
    var userInventory = <?php echo json_encode($user_inventory); ?>;
    var recipeCounter = 0;
    var selectedRecipes = {};
    
    // 레시피에서 사용 가능한 최대 제작 횟수 계산
    function calculateMaxCrafts(recipe) {
        if (!recipe || !recipe.material_status || recipe.material_status.length === 0) return 0;
        
        let maxCount = 999;
        for (let material of recipe.material_status) {
            // 안전한 값 확인
            let have = material.have || material.owned || 0;
            let required = material.required || 0;
            
            if (required <= 0) continue; // 필요 수량이 0이면 스킵
            if (have < required) return 0;
            
            let possible = Math.floor(have / required);
            maxCount = Math.min(maxCount, possible);
        }
        return Math.min(maxCount, 10); // 최대 10회로 제한
    }
    
    // 레시피 아이템 추가
    function addRecipeItem() {
        var index = recipeCounter++;
        var html = '<div class="recipe-item" data-index="' + index + '" style="margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">';
        html += '<div style="display: flex; gap: 10px; align-items: center;">';
        html += '<select name="recipe_select_' + index + '" onchange="updateRecipeItem(' + index + ', this.value)" style="flex: 1;">';
        html += '<option value="">레시피를 선택하세요</option>';
        
        for (let recipe of availableRecipes) {
            // rc_id 확인 (숫자로 변환)
            let recipeId = parseInt(recipe.rc_id) || parseInt(recipe.id) || 0;
            let recipeName = recipe.rc_name || recipe.name || '알 수 없는 레시피';
            let resultItemName = recipe.result_item_name || '알 수 없는 아이템';
            let resultQty = recipe.rc_result_quantity || 1;
            let successRate = recipe.rc_success_rate || 0;
            
            if (recipeId === 0) {
                continue; // ID가 0인 레시피는 건너뛰기
            }
            
            let disabled = !recipe.has_materials ? 'disabled' : '';
            let suffix = !recipe.has_materials ? ' (재료 부족)' : ' (성공률: ' + successRate + '%)';
            html += '<option value="' + recipeId + '" ' + disabled + '>';
            html += recipeName + ' → ' + resultItemName + ' ×' + resultQty + suffix;
            html += '</option>';
        }
        
        html += '</select>';
        html += '<input type="number" name="recipe_count_' + index + '" min="1" max="10" value="1" style="width: 80px;" onchange="updateRecipeCount(' + index + ', this.value)">';
        html += '<span class="material-status" id="status_' + index + '"></span>';
        html += '<button type="button" onclick="removeRecipeItem(' + index + ')" class="btn btn-sm btn-danger">X</button>';
        html += '</div>';
        html += '<div id="recipe_detail_' + index + '" style="margin-top: 10px; font-size: 0.9em; color: #666;"></div>';
        html += '</div>';
        
        document.getElementById('recipe-container').insertAdjacentHTML('beforeend', html);
    }
    
    // 레시피 아이템 업데이트
    function updateRecipeItem(index, recipeId) {
        if (!recipeId) {
            delete selectedRecipes[index];
            document.getElementById('status_' + index).innerHTML = '';
            document.getElementById('recipe_detail_' + index).innerHTML = '';
        } else {
            // 타입 변환하여 비교 (문자열과 숫자 모두 처리)
            var recipe = availableRecipes.find(r => {
                var rid = r.rc_id || r.id || 0;
                return String(rid) === String(recipeId);
            });
            if (recipe) {
                var maxCrafts = calculateMaxCrafts(recipe);
                var parsedId = parseInt(recipeId) || parseInt(recipe.rc_id) || 0;
                selectedRecipes[index] = {
                    id: parsedId,  // 숫자로 변환
                    recipe: recipe,
                    count: 1,
                    maxCrafts: maxCrafts
                };
                var countInput = document.querySelector('input[name="recipe_count_' + index + '"]');
                countInput.max = maxCrafts;
                if (countInput.value > maxCrafts) countInput.value = maxCrafts;
                
                updateRecipeStatus(index);
            }
        }
        updateSummary();
    }
    
    // 제작 횟수 업데이트
    function updateRecipeCount(index, count) {
        if (selectedRecipes[index]) {
            selectedRecipes[index].count = Math.min(parseInt(count), selectedRecipes[index].maxCrafts);
            updateRecipeStatus(index);
            updateSummary();
        }
    }
    
    // 레시피 상태 표시 업데이트
    function updateRecipeStatus(index) {
        var item = selectedRecipes[index];
        if (!item) return;
        
        var statusEl = document.getElementById('status_' + index);
        var detailEl = document.getElementById('recipe_detail_' + index);
        
        statusEl.innerHTML = '<span style="color: green;">✓ 최대 ' + item.maxCrafts + '회 가능</span>';
        
        // 재료 상세 표시
        var detailHtml = '<div style="padding: 5px; background: #f5f5f5; border-radius: 3px;">';
        detailHtml += '<strong>필요 재료:</strong><br>';
        
        if (item.recipe.material_status && item.recipe.material_status.length > 0) {
            for (let mat of item.recipe.material_status) {
                let itemName = mat.it_name || mat.item_name || '알 수 없는 아이템';
                let have = mat.have || mat.owned || 0;
                let required = mat.required || 0;
                let totalNeeded = required * item.count;
                let color = have >= totalNeeded ? 'green' : 'red';
                detailHtml += '• ' + itemName + ': <span style="color:' + color + '">' + have + '/' + totalNeeded + '</span><br>';
            }
        } else {
            detailHtml += '<span style="color: gray;">재료 정보 없음</span><br>';
        }
        
        detailHtml += '</div>';
        detailEl.innerHTML = detailHtml;
    }
    
    // 레시피 아이템 제거
    function removeRecipeItem(index) {
        document.querySelector('.recipe-item[data-index="' + index + '"]').remove();
        delete selectedRecipes[index];
        updateSummary();
    }
    
    // 전체 요약 업데이트
    function updateSummary() {
        var summaryEl = document.getElementById('recipe-summary');
        var contentEl = document.getElementById('recipe-summary-content');
        
        var items = Object.values(selectedRecipes);
        if (items.length === 0) {
            summaryEl.style.display = 'none';
            document.getElementById('recipes_json').value = '';
            return;
        }
        
        var summaryHtml = '<ul>';
        var totalAttempts = 0;
        
        for (let item of items) {
            let recipeName = item.recipe.rc_name || item.recipe.name || '알 수 없는 레시피';
            let successRate = item.recipe.rc_success_rate || item.recipe.success_rate || 0;
            summaryHtml += '<li>' + recipeName + ' × ' + item.count + '회';
            summaryHtml += ' (성공률: ' + successRate + '%)</li>';
            totalAttempts += item.count;
        }
        summaryHtml += '</ul>';
        summaryHtml += '<strong>총 ' + totalAttempts + '회 조합 시도</strong>';
        
        contentEl.innerHTML = summaryHtml;
        summaryEl.style.display = 'block';
        
        // JSON 데이터 준비
        var recipesData = items.map(item => {
            return {
                id: parseInt(item.id) || 0,  // 숫자로 확실히 변환
                count: parseInt(item.count) || 1
            };
        });
        document.getElementById('recipes_json').value = JSON.stringify(recipesData);
    }
    
    // 페이지 로드 시 버튼 이벤트 바인딩
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('add-recipe-btn').addEventListener('click', addRecipeItem);
    });
    </script>
    <?php } ?>

    <div class="bo_w_tit write_div">
        <label for="wr_subject" class="sound_only">제목<strong>필수</strong></label>
        <div id="autosave_wrapper">
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input required" size="50" maxlength="255" placeholder="제목">
            <?php if ($is_member) { // 임시 저장된 글 기능 ?>
            <script src="<?php echo G5_JS_URL; ?>/autosave.js"></script>
            <button type="button" id="btn_autosave_save" class="btn_frmline" onclick="autosave();">임시저장</button>
            <button type="button" id="btn_autosave" class="btn_frmline">임시저장된 글 (<span id="autosave_count"><?php echo $autosave_count; ?></span>)</button>
            <div id="autosave_pop">
                <strong>임시 저장된 글 목록</strong>
                <ul></ul>
                <div><button type="button" class="autosave_close">X</button></div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- 본문 + 파일 첨부 (write_file.common.php) -->
    <?php include_once(G5_BBS_PATH . '/write_file.common.php'); ?>

    <div class="btn_confirm write_div">
        <a href="<?php echo get_pretty_url($bo_table); ?>" class="btn_cancel btn">취소</a>
        <button type="submit" id="btn_submit" accesskey="s" class="btn_submit btn">작성완료</button>
    </div>
    </form>

    <script>
    <?php if($write_min || $write_max) { ?>
    // 글자수 제한
    var char_min = parseInt(<?php echo $write_min; ?>); // 최소
    var char_max = parseInt(<?php echo $write_max; ?>); // 최대
    check_byte('wr_content', 'char_count');

    $(function() {
        $("#wr_content").on("keyup", function() {
            check_byte('wr_content', 'char_count');
        });
    });
    <?php } ?>

    function html_auto_br(obj)
    {
        if (obj.checked) {
            result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
            if (result)
                obj.value = "html2";
            else
                obj.value = "html1";
        }
        else
            obj.value = "";
    }

    function fwrite_submit(f)
    {
        <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

        // 복수 레시피가 선택된 경우 확인
        var recipesJson = document.getElementById('recipes_json');
        if (recipesJson && recipesJson.value) {
            var recipes = JSON.parse(recipesJson.value);
            if (recipes.length > 0) {
                var totalAttempts = recipes.reduce((sum, r) => sum + r.count, 0);
                if (!confirm('총 ' + totalAttempts + '회의 조합을 진행하시겠습니까?\n재료가 소모되며, 실패 시 반환되지 않습니다.')) {
                    return false;
                }
            }
        }

        var subject = "";
        var content = "";
        $.ajax({
            url: g5_bbs_url+"/ajax.filter.php",
            type: "POST",
            data: {
                "subject": f.wr_subject.value,
                "content": f.wr_content.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data, textStatus) {
                subject = data.subject;
                content = data.content;
            }
        });

        if (subject) {
            alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
            f.wr_subject.focus();
            return false;
        }

        if (content) {
            alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
            if (typeof(ed_wr_content) != "undefined")
                ed_wr_content.returnFalse();
            else
                f.wr_content.focus();
            return false;
        }

        if (document.getElementById("char_count")) {
            if (char_min > 0 || char_max > 0) {
                var cnt = parseInt(check_byte('wr_content', 'char_count'));
                if (char_min > 0 && char_min > cnt) {
                    alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다.");
                    return false;
                }
                else if (char_max > 0 && char_max < cnt) {
                    alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다.");
                    return false;
                }
            }
        }

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
    </script>
</section>
<!-- } 게시물 작성/수정 끝 -->
