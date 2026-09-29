<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/session.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/session.cclog.css">', 1);

// 세션카드 이미지 관련 설정
$trpg_up_table = G5_TABLE_PREFIX . 'trpg_up';

// 세션 카드 이미지 가져오기
$card_images = array();
$sql = "SELECT * FROM {$trpg_up_table} 
        WHERE bo_table = '{$bo_table}' 
        AND wr_id = '{$view['wr_id']}' 
        AND image_type = 'card' 
        AND img_use = '1'";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $card_images[] = array(
        'id' => $row['id'],
        'image_url' => $row['image_url'],
        'wr_type' => $row['wr_type']
    );
}

// 캐릭터 이미지 가져오기
$cha_images = array();
$sql = "SELECT * FROM {$trpg_up_table} 
        WHERE bo_table = '{$bo_table}' 
        AND wr_id = '{$view['wr_id']}' 
        AND image_type = 'cha' 
        AND img_use = '1' 
        ORDER BY file_order ASC";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $cha_images[] = array(
        'id' => $row['id'],
        'image_url' => $row['image_url'],
        'file_order' => $row['file_order']
    );
}

// 일반 이미지 가져오기
$normal_images = array();
$sql = "SELECT * FROM {$trpg_up_table} 
        WHERE bo_table = '{$bo_table}' 
        AND wr_id = '{$view['wr_id']}' 
        AND image_type = 'normal' 
        AND img_use = '1' 
        ORDER BY file_order ASC";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $normal_images[] = array(
        'id' => $row['id'],
        'image_url' => $row['image_url'],
        'file_order' => $row['file_order']
    );
}

// RA0 표준 비밀글 처리
$is_secret = (isset($view['wr_option']) && strpos($view['wr_option'], 'secret') !== false);
$session_key = 'ss_secret_'.$bo_table.'_'.$view['wr_num'];
$has_session = get_session($session_key);
$is_owner = ($member['mb_id'] && $member['mb_id'] === $view['mb_id']);

if ($is_secret && !$has_session && !($is_admin || $is_owner)) {
    // 비밀글이고 권한이 없으면 비밀번호 입력 페이지로 이동
    $p_url = G5_BBS_URL."/password_check.php?w=s&amp;bo_table=".$bo_table."&amp;wr_id=".$view['wr_id'].$qstr;
    goto_url($p_url);
}

function processContent($outline) {
    global $bo_table, $wr_id;
    $outline = stripslashes($outline);

    return $outline;
}

$trpg_outline = processContent($view['wr_5']);

// RA0 디자인 설정에서 기본 색상 가져오기
$design = get_design_config();
$trbg_color = !empty($view['wr_2']) ? $view['wr_2'] : ($design['card_bg_color'] ?? '#222222');
$trtxt_color = !empty($view['wr_3']) ? $view['wr_3'] : ($design['content_font_color'] ?? '#eeeeee');

$session_kpc = isset($view['wr_kpc']) ? trim((string)$view['wr_kpc']) : '';
$session_pc = isset($view['wr_pc']) ? trim((string)$view['wr_pc']) : '';
?>

<style>
:root {
    --trbg-color: <?php echo $trbg_color; ?>;
    --trtxt-color: <?php echo $trtxt_color; ?>;
}

::-webkit-scrollbar {
    display: block;
}
</style>

<div class="trpg-theme-container">
    
<!-- 핸드아웃 버튼 추가 -->
<button id="handoutToggle" class="handout-button">
    <i class="fa-solid fa-book-open"></i>
</button>

<!-- 링크 버튼 시작 -->
<div class="trpg-viewoption-btn">
    <?php if ($update_href) { ?>
    <a href="<?php echo $update_href ?>&amp;trpg_edit_mode=settings" class="trpg-btn ui" title="로그 페이지 설정" aria-label="로그 페이지 설정"><i class="fa-solid fa-gear"></i></a>
    <a href="<?php echo $update_href ?>&amp;trpg_edit_mode=content" class="trpg-btn ui" title="로그 수정" aria-label="로그 수정"><i class="fa-solid fa-pen-to-square"></i></a>
    <?php } ?>
    <?php if ($delete_href) { ?><a href="<?php echo $delete_href ?>" class="trpg-btn ui admin" onclick="del(this.href); return false;"><i class="fa-solid fa-trash"></i></a><?php } ?>
    <a href="<?php echo G5_BBS_URL; ?>/share_popup.php?bo_table=<?php echo $bo_table; ?>&wr_id=<?php echo $view['wr_id']; ?>" class="trpg-btn ui" onclick="window.open(this.href, 'share_popup', 'width=500,height=400'); return false;" title="공유"><i class="fa-solid fa-share-from-square"></i></a>
    <a href="<?php echo $list_href ?>" class="trpg-btn ui"><i class="fa-solid fa-list"></i></a>
    <a href="javascript:void(0);" onclick="scrollToTop()" class="trpg-btn ui"><i class="fa-solid fa-arrow-up"></i></a>
    <a href="javascript:void(0);" onclick="scrollToBottom()" class="trpg-btn ui"><i class="fa-solid fa-arrow-down"></i></a>
</div>
<!-- 링크 버튼 끝 -->

<?php
$bg_image = !empty($view['wr_url']) ? $view['wr_url'] : '';
?>
<?php if($bg_image) { ?>
<div class="background-container" style="background-image: url('<?php echo $bg_image; ?>');"></div>
<div class="bg-effect"></div>
<?php } ?>

<div class="trpg-theme-box<?php G5_IS_MOBILE ? ' mobile' : '' ?>">
    <!-- 카테고리 (존재하면) -->
    <?php if ($is_category) { ?><div class="cate"><?php $view['ca_name'] ?></div><?php } ?>

    <?php
    // 세션 카드 이미지 출력
    if (!empty($card_images)) {
        echo '<div class="thumbnail-box">';
        foreach ($card_images as $card) {
            $img_url = htmlspecialchars($card['image_url'], ENT_QUOTES);
            echo "<div class='thumbnail'><img src='{$img_url}' alt=''></div>";
        }
        echo '</div>';
    }
    ?>

    <!-- 타이틀 -->
    <div class="trpg-maintitle" style="font-family: '<?php echo $view['wr_8'] ? $view['wr_8'] : 'Pretendard-Regular'; ?>', sans-serif;">
        <?php echo $view['wr_subject'] ?>
    </div>

    <!-- 부제 출력 -->
    <?php if (!empty($view['wr_title'])) { ?>
        <div class="trpg-subtitle"><?php echo stripslashes($view['wr_title']); ?></div> 
    <?php } ?>

    <!-- 세션 정보 (날짜, 인원) -->
    <div class="session-info">
        <?php if (!empty($view['wr_7'])) { ?>
            <div class="session-date">
                <i class="fa-regular fa-calendar"></i> 
                <?php 
                $dates = explode(' ~ ', $view['wr_7']);
                echo $dates[0];
                if (isset($dates[1])) {
                    echo ' ~ ' . $dates[1];
                }
                ?>
            </div>
        <?php } ?>
    </div>
    <!-- 참여자 섹션 -->
    <?php if ($session_kpc !== '' || $session_pc !== '') { ?>
    <div class="participants-section">
        <div class="session-members">
            <i class="fa-solid fa-users"></i>
            <?php if ($session_kpc !== '') { ?><span>KPC <?php echo htmlspecialchars($session_kpc, ENT_QUOTES); ?></span><?php } ?>
            <?php if ($session_kpc !== '' && $session_pc !== '') { ?><span> · </span><?php } ?>
            <?php if ($session_pc !== '') { ?><span>PC <?php echo htmlspecialchars($session_pc, ENT_QUOTES); ?></span><?php } ?>
        </div>
        <div class="participants-list">
            <?php
            if (!empty($cha_images)) {
                foreach ($cha_images as $index => $image) {
                    $img_url = $image['image_url'];
                    echo "<div class='participant'>";
                    echo "<div class='participant-avatar' data-index='{$index}'>";
                    echo "<img src='" . htmlspecialchars($img_url, ENT_QUOTES) . "' class='avatar-img' alt='' "
                        . "data-image-src='" . htmlspecialchars($img_url, ENT_QUOTES) . "'>";
                    echo "</div>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>
    <?php } ?>

    <!-- 개요 섹션 -->
    <?php if (!empty($view['wr_5'])) { ?>
    <hr class="hr-line">

    <div class="outline-section">
        <div class="outline-part">
            <?php echo nl2br(stripslashes($trpg_outline)); ?>
        </div>
    </div>
    <?php } ?>

    <hr class="hr-line">

    <!-- ✅ 본문 텍스트 출력 -->
    <div class="content-box<?php G5_IS_MOBILE ? ' mobile' : '' ?>">
        <div class="content_area">
            <?php 
            // 1. 백슬래시 제거
            $outline = str_replace('\\', '', $view['wr_content']);

            // 5. 출력
            echo $outline;
            ?>
        </div>
    </div> <!-- 콘텐츠 끝 -->
</div>

<!-- 핸드아웃 모달 -->
<div id="handoutSidebar" class="handout-sidebar">
    <div class="handout-sidebar-header">
        <h3>핸드아웃</h3>
        <span class="close" onclick="toggleHandoutSidebar()">&times;</span>
    </div>
    <div class="handout-sidebar-content">
        <div class="handout-scroll">
            <?php
            if (!empty($normal_images)) {
                foreach ($normal_images as $index => $image) {
                    $img_url = $image['image_url'];
                    echo "<div class='handout-item'>";
                    echo "<img src='" . htmlspecialchars($img_url, ENT_QUOTES) . "' class='handout-img' alt='' "
                        . "data-image-src='" . htmlspecialchars($img_url, ENT_QUOTES) . "' data-type='handout' data-index='{$index}'>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>
</div>

<!-- 모달 추가 (캐릭터 이미지와 핸드아웃 이미지 전용) -->
<div id="characterModal" class="modal">
    <span class="close" onclick="closeCharacterModal()">&times;</span>
    <div class="modal-content">
        <img id="characterModalImg">
    </div>
</div>

<div id="handoutModal" class="modal">
    <span class="close" onclick="closeHandoutModal()">&times;</span>
    <span class="prev" onclick="prevHandout()">&#10094;</span>
    <div class="modal-content">
        <img id="handoutModalImg">
    </div>
    <span class="next" onclick="nextHandout()">&#10095;</span>
</div>

<script>
// 캐릭터 모달 관련 변수
var characterImages = <?php echo json_encode(array_column($cha_images, 'image_url')); ?>;
var currentCharacterIndex = 0;

// 핸드아웃 모달 관련 변수
var handoutImages = <?php echo json_encode(array_column($normal_images, 'image_url')); ?>;
var currentHandoutIndex = 0;

// 핸드아웃 사이드바 토글 함수
function toggleHandoutSidebar() {
    var sidebar = document.getElementById('handoutSidebar');
    sidebar.classList.toggle('active');
}

// 캐릭터 모달 함수들
function openCharacterModal(index) {
    currentCharacterIndex = index;
    document.getElementById('characterModalImg').src = characterImages[index];
    document.getElementById('characterModal').style.display = 'flex';
}

function closeCharacterModal() {
    document.getElementById('characterModal').style.display = 'none';
}

// 핸드아웃 모달 함수들
function openHandoutModal(index) {
    currentHandoutIndex = index;
    document.getElementById('handoutModalImg').src = handoutImages[index];
    document.getElementById('handoutModal').style.display = 'flex';
}

function closeHandoutModal() {
    document.getElementById('handoutModal').style.display = 'none';
}

function prevHandout() {
    if (currentHandoutIndex > 0) {
        currentHandoutIndex--;
        document.getElementById('handoutModalImg').src = handoutImages[currentHandoutIndex];
    }
}

function nextHandout() {
    if (currentHandoutIndex < handoutImages.length - 1) {
        currentHandoutIndex++;
        document.getElementById('handoutModalImg').src = handoutImages[currentHandoutIndex];
    }
}

// 페이지 로드 시 버튼 위치 설정
function setButtonPosition() {
  const themeBox = document.querySelector('.trpg-theme-box');
  const handoutButton = document.getElementById('handoutToggle');
  const optionButtons = document.querySelector('.trpg-viewoption-btn');
  
  if (themeBox && handoutButton && optionButtons) {
    const themeRect = themeBox.getBoundingClientRect();
    
    // 핸드아웃 버튼 위치 설정
    handoutButton.style.position = 'fixed';
    handoutButton.style.left = (themeRect.right - 1) + 'px';
    handoutButton.style.top = (themeRect.top + 30) + 'px';
    
    // 옵션 버튼들 위치 설정
    optionButtons.style.position = 'fixed';
    optionButtons.style.left = (themeRect.right - 10) + 'px';
    optionButtons.style.top = (themeRect.top + 75) + 'px';
    
    // 버튼들 표시
    handoutButton.style.display = 'flex';
    optionButtons.style.display = 'flex';
  }
}


// 최상단/최하단 스크롤 함수
function scrollToTop() {
    const themeBox = document.querySelector('.trpg-theme-box');
    if (themeBox) {
        themeBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function scrollToBottom() {
    const themeBox = document.querySelector('.trpg-theme-box');
    if (themeBox) {
        themeBox.scrollIntoView({ behavior: 'smooth', block: 'end' });
        
        // 콘텐츠 영역의 끝으로 스크롤 (더 정확한 위치)
        const contentBox = document.querySelector('.content-box');
        if (contentBox) {
            setTimeout(() => {
                contentBox.scrollIntoView({ behavior: 'smooth', block: 'end' });
            }, 100);
        }
    }
}

// 이벤트 리스너 설정
document.addEventListener('DOMContentLoaded', function() {

    setButtonPosition();

    // 핸드아웃 토글 버튼 이벤트
    document.getElementById('handoutToggle').addEventListener('click', toggleHandoutSidebar);
    
    // 캐릭터 아바타 클릭 이벤트
    document.querySelectorAll('.participant-avatar').forEach(function(avatar) {
        avatar.addEventListener('click', function() {
            var index = parseInt(this.getAttribute('data-index'));
            openCharacterModal(index);
        });
    });
    
    // 핸드아웃 이미지 클릭 이벤트
    document.querySelectorAll('.handout-img').forEach(function(img) {
        img.addEventListener('click', function() {
            var index = parseInt(this.getAttribute('data-index'));
            openHandoutModal(index);
        });
    });
    
    // 모달 바깥 클릭 시 닫기
    document.getElementById('characterModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCharacterModal();
        }
    });
    
    document.getElementById('handoutModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeHandoutModal();
        }
    });
    
    // 키보드 이벤트
    document.addEventListener('keydown', function(e) {
        // 캐릭터 모달이 열려있을 때
        if (document.getElementById('characterModal').style.display === 'flex') {
            if (e.key === 'Escape') {
                closeCharacterModal();
            }
        }
        
        // 핸드아웃 모달이 열려있을 때
        if (document.getElementById('handoutModal').style.display === 'flex') {
            if (e.key === 'Escape') {
                closeHandoutModal();
            } else if (e.key === 'ArrowLeft') {
                prevHandout();
            } else if (e.key === 'ArrowRight') {
                nextHandout();
            }
        }
        
        // 핸드아웃 사이드바가 열려있을 때
        if (document.getElementById('handoutSidebar').classList.contains('active')) {
            if (e.key === 'Escape') {
                toggleHandoutSidebar();
            }
        }
    });
});

window.addEventListener('resize', setButtonPosition);
</script>

<?php if (!empty($view['wr_10'])) { ?>

<!-- 유튜브 API 스크립트 추가 -->
<script src="https://www.youtube.com/iframe_api"></script>

<div id="bgm-player"></div>
<div id="volume-control">
  <button id="playPauseBtn"><i class="fa-solid fa-play"></i></button>
  <input type="range" min="0" max="100" value="0" id="bgmVolume">
</div>

<script>
  let player;
  let isPlaying = false;

  function onYouTubeIframeAPIReady() {
    const videoURL = "<?php echo addslashes($view['wr_10']); ?>";
    const videoID = extractVideoID(videoURL);

    if (videoID) {
      player = new YT.Player('bgm-player', {
        height: '0',
        width: '0',
        videoId: videoID,
        playerVars: {
          autoplay: 1,
          loop: 1,
          playlist: videoID,
          controls: 0,
          modestbranding: 1
        },
        events: {
          'onReady': onPlayerReady
        }
      });
    }
  }

function extractVideoID(url) {
  const regExp = /^.*((youtu.be\/)|(v\/)|(\?v=)|(\&v=))([^#\&\?]*).*/;
  const match = url.match(regExp);
  return (match && match[6].length == 11) ? match[6] : null;
}

function onPlayerReady(event) {
  player.setVolume(0);
  event.target.playVideo();
  isPlaying = true;
  document.getElementById('playPauseBtn').innerHTML = '<i class="fa-solid fa-pause"></i>';

  document.addEventListener('mousemove', increaseVolumeOnInteraction, { once: true });
}

function increaseVolumeOnInteraction() {
  player.setVolume(50);
  document.getElementById('bgmVolume').value = 50;
}

document.getElementById('bgmVolume').addEventListener('input', function() {
  const volume = parseInt(this.value);
  if (player && player.setVolume) {
    player.setVolume(volume);
  }
});

document.getElementById('playPauseBtn').addEventListener('click', function() {
  if (!isPlaying) {
    player.playVideo();
    this.innerHTML = '<i class="fa-solid fa-pause"></i>';
    isPlaying = true;
  } else {
    player.pauseVideo();
    this.innerHTML = '<i class="fa-solid fa-play"></i>';
    isPlaying = false;
  }
});
</script>

<?php } ?>

</div><!-- trpg-theme-container -->

<style>
    #body {
        min-height: fit-content;
    }
</style>
