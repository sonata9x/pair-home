<?php
include_once('./_common.php');

// BGM iframe으로 독립 실행

// BGM 설정이 없으면 빈 페이지
if (empty($design['site_bgm_url'])) {
    return;
}

// YouTube URL에서 ID 추출
function getYouTubeId($url) {
    if (empty($url)) return null;

    // 재생목록 파라미터 확인 (list= 파라미터로 감지)
    if (preg_match('/[?&]list=([a-zA-Z0-9_-]+)/', $url, $listMatches)) {
        // 시작 영상 ID도 함께 추출
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $videoMatches);
        return [
            'type' => 'playlist',
            'id' => $listMatches[1],
            'start_video' => isset($videoMatches[1]) ? $videoMatches[1] : null
        ];
    }

    // 단일 영상 URL
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    return isset($matches[1]) ? ['type' => 'video', 'id' => $matches[1]] : null;
}

$youtube_data = getYouTubeId($design['site_bgm_url']);

// BGM 스킨 설정
$bgm_skin = isset($design['bgm_skin']) ? $design['bgm_skin'] : '';
$bgm_skin_path = G5_SKIN_PATH . '/bgm/' . $bgm_skin;
$bgm_skin_url = G5_SKIN_URL . '/bgm/' . $bgm_skin;
$use_skin = $bgm_skin && is_dir($bgm_skin_path) && file_exists($bgm_skin_path . '/bgm.skin.php');

// 스킨에서 사용할 설정 데이터
$bgmConfig = [
    'youtube' => $youtube_data,
    'autoplay' => 0,
    'volume' => 30
];
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BGM Player</title>
    <?php if ($use_skin): ?>
    <link rel="stylesheet" href="<?php echo $bgm_skin_url ?>/style.css">
    <?php else: ?>
    <link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/bgm.css">
    <?php endif; ?>
</head>
<body>
    <?php if ($use_skin): ?>
    <!-- 스킨 사용 -->
    <?php include_once($bgm_skin_path . '/bgm.skin.php'); ?>
    <?php else: ?>
    <!-- 기본 BGM Player HTML -->
    <div id="bgm-player" class="bgm-player">
        <div class="minimized-overlay" onclick="togglePlayer()"></div>

        <div class="player-content">
            <div class="bgm-controls">
                <div class="player-controls">
                    <button class="control-btn" id="prev-btn" onclick="previousTrack()" disabled><i class="fa-solid fa-backward"></i></button>
                    <button class="control-btn play-pause" id="play-btn" onclick="togglePlay()"><i class="fa-solid fa-play"></i></button>
                    <button class="control-btn" id="next-btn" onclick="nextTrack()" disabled><i class="fa-solid fa-forward"></i></button>
                </div>

                <div class="mode-controls">
                    <button class="control-btn mode-btn" id="shuffle-btn" onclick="toggleShuffle()" title="셔플"><i class="fa-solid fa-shuffle"></i></button>
                    <button class="control-btn mode-btn active" id="repeat-btn" onclick="cycleRepeatMode()" title="반복"><i class="fa-solid fa-repeat"></i></button>
                </div>

                <div class="volume-control">
                    <button class="control-btn volume-btn" id="volume-btn" onclick="toggleVolume()" title="볼륨"><i class="fa-solid fa-volume-high"></i></button>
                    <div class="volume-popup" id="volume-popup">
                        <input type="range" class="volume-slider" id="volume-slider" min="0" max="100" value="30" orient="vertical">
                    </div>
                </div>
            </div>

            <div class="track-header">
                <div class="track-info" id="track-info">BGM Loading...</div>
                <button class="list-toggle-btn" id="list-toggle-btn" onclick="togglePlaylist()" style="display: none;">
                    <span class="toggle-icon"><i class="fa-solid fa-list-ul"></i></span>
                </button>
            </div>

            <div class="playlist-container" id="playlist-container" style="display: none;">
                <ul class="playlist" id="playlist"></ul>
            </div>

            <button class="minimize-btn" onclick="togglePlayer()"><i class="fa-solid fa-chevron-down"></i></button>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // BGM 설정 데이터
        const bgmConfig = <?php echo json_encode($bgmConfig); ?>;
    </script>

    <script src="<?php echo G5_JS_URL ?>/bgm.js"></script>
</body>
</html>
