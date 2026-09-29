let player;
let playerReady = false;
let isPlaying = false;
let currentVolume = 30;
let isMinimized = false;
let userInteracted = false;
let readyToPlay = false;
let isPlaylistOpen = false;
let playlistData = [];
let currentTrackIndex = 0;

// 셔플/반복 관련 변수
let isShuffleOn = false;
let repeatMode = 'all'; // 'all', 'one', 'none'
let shuffledOrder = []; // 셔플된 인덱스 순서
let shuffledIndex = 0;  // 셔플 순서에서의 현재 위치
let isVolumeOpen = false;

// localStorage 키
const BGM_STORAGE_KEY = 'ra0_bgm_settings';

// localStorage 저장
function saveBGMSettings() {
    try {
        const settings = {
            volume: currentVolume,
            shuffle: isShuffleOn,
            repeat: repeatMode
        };
        localStorage.setItem(BGM_STORAGE_KEY, JSON.stringify(settings));
    } catch (e) {
    }
}

// localStorage 복원
function loadBGMSettings() {
    try {
        const saved = localStorage.getItem(BGM_STORAGE_KEY);
        if (saved) {
            const settings = JSON.parse(saved);
            if (typeof settings.volume === 'number') {
                currentVolume = settings.volume;
            }
            if (typeof settings.shuffle === 'boolean') {
                isShuffleOn = settings.shuffle;
            }
            if (['all', 'one', 'none'].includes(settings.repeat)) {
                repeatMode = settings.repeat;
            }
        }
    } catch (e) {
    }
}

// 셔플 순서 생성
function generateShuffledOrder() {
    if (!playlistData.length) return;

    shuffledOrder = [...Array(playlistData.length).keys()];
    // Fisher-Yates 셔플
    for (let i = shuffledOrder.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffledOrder[i], shuffledOrder[j]] = [shuffledOrder[j], shuffledOrder[i]];
    }

    // 현재 곡을 첫 번째로 이동
    const currentPos = shuffledOrder.indexOf(currentTrackIndex);
    if (currentPos > 0) {
        shuffledOrder.splice(currentPos, 1);
        shuffledOrder.unshift(currentTrackIndex);
    }
    shuffledIndex = 0;
}

// 셔플 토글
function toggleShuffle() {
    isShuffleOn = !isShuffleOn;

    if (isShuffleOn) {
        generateShuffledOrder();
    }

    updateShuffleUI();
    saveBGMSettings();
}

// 반복 모드 순환
function cycleRepeatMode() {
    if (repeatMode === 'all') {
        repeatMode = 'one';
    } else if (repeatMode === 'one') {
        repeatMode = 'none';
    } else {
        repeatMode = 'all';
    }

    updateRepeatUI();
    saveBGMSettings();
}

// 셔플 UI 업데이트
function updateShuffleUI() {
    const shuffleBtn = document.getElementById('shuffle-btn');
    if (shuffleBtn) {
        if (isShuffleOn) {
            shuffleBtn.classList.add('active');
        } else {
            shuffleBtn.classList.remove('active');
        }
    }
}

// 반복 UI 업데이트
function updateRepeatUI() {
    const repeatBtn = document.getElementById('repeat-btn');
    if (!repeatBtn) return;

    repeatBtn.classList.remove('active', 'repeat-one');

    if (repeatMode === 'all') {
        repeatBtn.classList.add('active');
        repeatBtn.innerHTML = '<i class="fa-solid fa-repeat"></i>';
    } else if (repeatMode === 'one') {
        repeatBtn.classList.add('active', 'repeat-one');
        repeatBtn.innerHTML = '<i class="fa-solid fa-repeat"></i><span class="repeat-one-badge">1</span>';
    } else {
        repeatBtn.innerHTML = '<i class="fa-solid fa-repeat"></i>';
    }
}

// 볼륨 팝업 토글
function toggleVolume() {
    isVolumeOpen = !isVolumeOpen;
    const popup = document.getElementById('volume-popup');
    if (popup) {
        if (isVolumeOpen) {
            popup.classList.add('show');
        } else {
            popup.classList.remove('show');
        }
    }
}

// 볼륨 아이콘 업데이트
function updateVolumeIcon() {
    const volumeBtn = document.getElementById('volume-btn');
    if (!volumeBtn) return;

    let icon = 'fa-volume-high';
    if (currentVolume == 0) {
        icon = 'fa-volume-xmark';
    } else if (currentVolume < 30) {
        icon = 'fa-volume-off';
    } else if (currentVolume < 70) {
        icon = 'fa-volume-low';
    }

    volumeBtn.innerHTML = `<i class="fa-solid ${icon}"></i>`;
}

// Pixel 스킨 볼륨 바 업데이트 함수
function updatePixelVolumeBar(volume) {
    const pixelVolBar = document.getElementById('pixel-vol-bar');
    if (!pixelVolBar) return;

    const segments = pixelVolBar.querySelectorAll('.vol-seg');
    const filledCount = Math.round(volume / 10); // 0-100 → 0-10

    segments.forEach((seg, index) => {
        if (index < filledCount) {
            seg.classList.add('filled');
        } else {
            seg.classList.remove('filled');
        }
    });
}

// 다음 트랙 인덱스 계산 (셔플/반복 고려)
function getNextTrackIndex() {
    if (playlistData.length <= 1) return 0;

    if (repeatMode === 'one') {
        return currentTrackIndex;
    }

    if (isShuffleOn) {
        shuffledIndex++;
        if (shuffledIndex >= shuffledOrder.length) {
            if (repeatMode === 'none') {
                return -1; // 재생 중지
            }
            shuffledIndex = 0;
            generateShuffledOrder(); // 새로운 셔플 순서
        }
        return shuffledOrder[shuffledIndex];
    } else {
        const nextIndex = currentTrackIndex + 1;
        if (nextIndex >= playlistData.length) {
            if (repeatMode === 'none') {
                return -1; // 재생 중지
            }
            return 0;
        }
        return nextIndex;
    }
}

// 이전 트랙 인덱스 계산 (셔플/반복 고려)
function getPrevTrackIndex() {
    if (playlistData.length <= 1) return 0;

    if (isShuffleOn) {
        shuffledIndex--;
        if (shuffledIndex < 0) {
            shuffledIndex = shuffledOrder.length - 1;
        }
        return shuffledOrder[shuffledIndex];
    } else {
        return currentTrackIndex > 0 ? currentTrackIndex - 1 : playlistData.length - 1;
    }
}

// 보안 검증 함수들
function validateOrigin() {
    // 현재 실행 중인 도메인은 항상 허용 (자동 감지)
    const currentOrigin = window.location.hostname;

    // localhost 또는 실제 도메인(점이 포함된 hostname)이면 허용
    if (currentOrigin === 'localhost' || currentOrigin.includes('.')) {
        return true;
    }

    // 그 외에는 거부 (비정상적인 경우)
    return false;
}

// 보안 YouTube API 준비
function onYouTubeIframeAPIReady() {
    if (!validateOrigin()) {
        return;
    }
    
    initBGMPlayer();
}

async function initBGMPlayer() {
    // 추가 보안 검증
    if (!validateOrigin()) {
        return;
    }
    
    if (!bgmConfig.youtube) {
        return;
    }
    
    // YouTube Player 생성 (보안 강화된 설정)
    let playerDiv = document.getElementById('youtube-player');
    if (!playerDiv) {
        playerDiv = document.createElement('div');
        playerDiv.id = 'youtube-player';
        playerDiv.style.display = 'none';
        document.body.appendChild(playerDiv);
    }
    
    // 보안 강화된 playerVars
    // iframe 내부에서 실행될 때 최상위 페이지의 origin 사용
    let pageOrigin;
    try {
        pageOrigin = window.top.location.origin;
    } catch(e) {
        // cross-origin인 경우 현재 origin 사용
        pageOrigin = window.location.origin;
    }

    const securePlayerVars = {
        autoplay: 0,
        controls: 0,
        showinfo: 0,
        rel: 0,
        iv_load_policy: 3,
        enablejsapi: 1,
        modestbranding: 1,
        fs: 0,
        cc_load_policy: 0,
        disablekb: 1,
        origin: pageOrigin
    };
    
    if (bgmConfig.youtube.type === 'playlist') {
        player = new YT.Player('youtube-player', {
            height: '0',
            width: '0',
            playerVars: {
                ...securePlayerVars,
                listType: 'playlist',
                list: bgmConfig.youtube.id
            },
            events: {
                'onReady': onPlayerReady,
                'onStateChange': onPlayerStateChange,
                'onError': onPlayerError
            }
        });
    } else {
        player = new YT.Player('youtube-player', {
            height: '0',
            width: '0',
            videoId: bgmConfig.youtube.id,
            playerVars: securePlayerVars,
            events: {
                'onReady': onPlayerReady,
                'onStateChange': onPlayerStateChange,
                'onError': onPlayerError
            }
        });
    }
}

function onPlayerReady(event) {

    playerReady = true;
    readyToPlay = true;

    // 저장된 설정 복원
    loadBGMSettings();

    player.setVolume(currentVolume);

    // 볼륨 슬라이더 동기화
    const volumeSlider = document.getElementById('volume-slider');
    if (volumeSlider) {
        volumeSlider.value = currentVolume;
    }

    // 플레이어 표시
    setTimeout(() => {
        const bgmPlayer = document.getElementById('bgm-player');
        if (bgmPlayer) {
            bgmPlayer.classList.add('show');
        }
    }, 500);

    updateTrackInfo();
    updateControls();
    loadPlaylist();

    // 셔플/반복/볼륨 UI 초기화
    updateShuffleUI();
    updateRepeatUI();
    updateVolumeIcon();

    // 사용자 상호작용 대기
    setupUserInteractionListeners();
    showBGMNotice();
}

// 보안 강화된 플레이리스트 로드
async function loadPlaylist() {
    if (!playerReady) return;
    
    try {
        const playlist = player.getPlaylist();
        if (playlist && playlist.length > 1) {
            const toggleBtn = document.getElementById('list-toggle-btn');
            if (toggleBtn) {
                toggleBtn.style.display = 'flex';
            }
            
            playlistData = playlist;

            // 셔플이 켜져있으면 셔플 순서 생성
            if (isShuffleOn) {
                generateShuffledOrder();
            }

            await loadPlaylistTitles();
            updatePlaylistUI();
        }
    } catch (error) {
    }
}

// 보안 강화된 제목 가져오기
async function fetchVideoTitle(videoId) {
    // videoId 검증
    if (!videoId || typeof videoId !== 'string' || !/^[a-zA-Z0-9_-]{11}$/.test(videoId)) {
        return null;
    }
    
    try {
        // 안전한 URL 구성
        const safeUrl = `https://www.youtube.com/oembed?url=${encodeURIComponent(`https://www.youtube.com/watch?v=${videoId}`)}&format=json`;
        
        const response = await fetch(safeUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'omit',
            referrerPolicy: 'no-referrer'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        // 응답 데이터 검증
        if (data && typeof data.title === 'string' && data.title.trim() !== '') {
            // XSS 방지를 위한 제목 정리
            return data.title.replace(/<[^>]*>/g, '').trim();
        }
        
        return null;
    } catch (error) {
        return null;
    }
}

// 보안 강화된 사용자 상호작용 리스너
function setupUserInteractionListeners() {
    const events = ['click', 'touchstart', 'keydown', 'scroll', 'mousemove'];
    
    const handleUserInteraction = (e) => {
        // 이벤트 검증
        if (!e || !e.type) {
            return;
        }
        
        if (!userInteracted) {
            userInteracted = true;
            
            // 재생 전 최종 보안 검증
            if (readyToPlay && player && playerReady ) {
                startBGM();
            }
            
            hideBGMNotice();
            
            events.forEach(event => {
                document.removeEventListener(event, handleUserInteraction, true);
            });
        }
    };

    events.forEach(event => {
        document.addEventListener(event, handleUserInteraction, true);
    });
}

// 보안 강화된 BGM 시작
function startBGM() {
    if (!player || !playerReady) {
        return;
    }
    
    try {
        player.playVideo();
        
        setTimeout(() => {
            try {
                const newState = player.getPlayerState();
                
                if (newState === YT.PlayerState.PLAYING) {
                    // 재생 성공
                } else if (newState === YT.PlayerState.BUFFERING) {
                    // 버퍼링 중
                } else {
                    // 재생 실패 시 재시도
                    setTimeout(() => {
                        if (true) {
                            player.playVideo();
                        }
                    }, 1000);
                }
            } catch (error) {
            }
        }, 500);
        
    } catch (error) {
    }
}

// 보안 강화된 플레이어 상태 변경
function onPlayerStateChange(event) {
    const bgmPlayer = document.getElementById('bgm-player');

    if (event.data === YT.PlayerState.PLAYING) {
        isPlaying = true;
        const playBtn = document.getElementById('play-btn');
        if (playBtn) playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
        if (bgmPlayer) bgmPlayer.classList.add('playing');

        setTimeout(() => {
            updateTrackInfo();
            updateCurrentTrackTitle();
        }, 2000);

    } else if (event.data === YT.PlayerState.PAUSED) {
        isPlaying = false;
        const playBtn = document.getElementById('play-btn');
        if (playBtn) playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
        if (bgmPlayer) bgmPlayer.classList.remove('playing');
    } else if (event.data === YT.PlayerState.ENDED) {
        // 셔플/반복 모드에 따른 재생 처리
        if (playlistData.length > 1) {
            const nextIndex = getNextTrackIndex();

            if (nextIndex === -1) {
                // repeatMode === 'none' 이고 마지막 곡인 경우
                isPlaying = false;
                const playBtn = document.getElementById('play-btn');
                if (playBtn) playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
                return;
            }

            currentTrackIndex = nextIndex;

            setTimeout(() => {
                try {
                    player.playVideoAt(currentTrackIndex);
                } catch (error) {
                    player.seekTo(0);
                    player.playVideo();
                }
            }, 500);

            updatePlaylistUI();
            setTimeout(() => {
                updateTrackInfo();
                updateCurrentTrackTitle();
            }, 2000);
        } else {
            // 단일 곡
            if (repeatMode === 'none') {
                isPlaying = false;
                const playBtn = document.getElementById('play-btn');
                if (playBtn) playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
                return;
            }

            // 1곡 반복 또는 전체 반복 (단일곡은 동일)
            setTimeout(() => {
                try {
                    player.seekTo(0);
                    player.playVideo();
                } catch (error) {
                }
            }, 500);
        }
    }
    
    updateControls();
}

// 보안 강화된 플레이 토글
function togglePlay() {
    if (!playerReady || !true) return;

    if (!userInteracted) {
        userInteracted = true;
        hideBGMNotice();
    }

    try {
        if (isPlaying) {
            player.pauseVideo();
        } else {
            player.playVideo();
        }
    } catch (error) {
    }
}

function stopTrack() {
    if (!playerReady) return;

    try {
        player.stopVideo();
        isPlaying = false;
        updatePlayPauseButton();
    } catch (error) {
    }
}

// 트랙 이동 함수들 (셔플 지원)
function previousTrack() {
    if (!playerReady || playlistData.length <= 1) return;

    if (!userInteracted) {
        userInteracted = true;
        hideBGMNotice();
    }

    try {
        currentTrackIndex = getPrevTrackIndex();
        player.playVideoAt(currentTrackIndex);
        updatePlaylistUI();
        setTimeout(() => {
            updateTrackInfo();
            updateCurrentTrackTitle();
        }, 2000);
    } catch (error) {
    }
}

function nextTrack() {
    if (!playerReady || playlistData.length <= 1) return;

    if (!userInteracted) {
        userInteracted = true;
        hideBGMNotice();
    }

    try {
        const nextIndex = getNextTrackIndex();
        if (nextIndex === -1) {
            // repeatMode === 'none' 마지막 곡
            return;
        }
        currentTrackIndex = nextIndex;
        player.playVideoAt(currentTrackIndex);
        updatePlaylistUI();
        setTimeout(() => {
            updateTrackInfo();
            updateCurrentTrackTitle();
        }, 2000);
    } catch (error) {
    }
}

async function loadPlaylistTitles() {
    // 보안 체크 제거됨
    
    window.playlistTitles = new Array(playlistData.length).fill(null);
    
    const titlePromises = playlistData.map(async (videoId, index) => {
        try {
            const title = await fetchVideoTitle(videoId);
            window.playlistTitles[index] = title || `곡 ${index + 1}`;
            
            if (title) {
                updatePlaylistUI();
            }
        } catch (error) {
            window.playlistTitles[index] = `곡 ${index + 1}`;
        }
    });
    
    await Promise.all(titlePromises);
}

function updatePlaylistUI() {
    const playlistEl = document.getElementById('playlist');
    if (!playlistEl || !playlistData.length) return;
    
    playlistEl.innerHTML = '';
    
    playlistData.forEach((videoId, index) => {
        const li = document.createElement('li');
        
        let title;
        if (window.playlistTitles && window.playlistTitles[index]) {
            // XSS 방지
            title = window.playlistTitles[index].replace(/<[^>]*>/g, '');
        } else {
            title = `곡 ${index + 1} (로딩중...)`;
        }
            
        li.textContent = title;
        li.onclick = () => {
            // 클릭 시 보안 검증
            if (true) {
                playTrack(index);
            }
        };
        
        if (index === currentTrackIndex) {
            li.classList.add('current');
        }
        
        playlistEl.appendChild(li);
    });
}

function playTrack(index) {
    if (!playerReady || !playlistData.length || !true) return;
    
    try {
        player.playVideoAt(index);
        currentTrackIndex = index;
        updatePlaylistUI();
        
        setTimeout(() => {
            updateTrackInfo();
            updateCurrentTrackTitle();
        }, 2000);
    } catch (error) {
    }
}

async function updateCurrentTrackTitle() {
    if (!playerReady || !true) return;
    
    try {
        const videoData = player.getVideoData();
        
        if (videoData && videoData.title && videoData.title.trim() !== '') {
            if (window.playlistTitles) {
                // XSS 방지
                window.playlistTitles[currentTrackIndex] = videoData.title.replace(/<[^>]*>/g, '');
                updatePlaylistUI();
            }
            return;
        }
        
        if (playlistData[currentTrackIndex]) {
            const title = await fetchVideoTitle(playlistData[currentTrackIndex]);
            if (title && window.playlistTitles) {
                window.playlistTitles[currentTrackIndex] = title;
                updatePlaylistUI();
            }
        }
    } catch (error) {
    }
}

function togglePlaylist() {
    
    
    const container = document.getElementById('playlist-container');
    const toggleBtn = document.getElementById('list-toggle-btn');
    
    if (!container || !toggleBtn) return;
    
    isPlaylistOpen = !isPlaylistOpen;
    
    if (isPlaylistOpen) {
        container.style.display = 'block';
        toggleBtn.classList.add('active');
    } else {
        container.style.display = 'none';
        toggleBtn.classList.remove('active');
    }
}

function togglePlayer() {
    
    
    const playerElement = document.getElementById('bgm-player');
    if (!playerElement) return;
    
    isMinimized = !isMinimized;
    
    if (isMinimized) {
        playerElement.classList.add('minimized');
        if (isPlaylistOpen) {
            togglePlaylist();
        }
    } else {
        playerElement.classList.remove('minimized');
    }
}

function onPlayerError(event) {
    const trackInfo = document.getElementById('track-info');
    if (trackInfo) {
        trackInfo.textContent = '재생 오류 발생';
    }
}

function showBGMNotice() {
    if (document.getElementById('bgm-notice')) return;

    const notice = document.createElement('div');
    notice.id = 'bgm-notice';
    notice.innerHTML = `
        <div style="
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(0,0,0,0.9);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            z-index: 9999;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            animation: slideIn 0.3s ease;
        " onclick="handleNoticeClick()">
            페이지를 클릭하면 BGM이 재생됩니다
            <span style="margin-left: 10px; opacity: 0.7; font-size: 11px;">[클릭하여 닫기]</span>
        </div>
        <style>
            @keyframes slideIn {
                from { transform: translateY(100px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        </style>
    `;
    document.body.appendChild(notice);

    setTimeout(() => {
        hideBGMNotice();
    }, 10000);
}

function handleNoticeClick() {
    if (!userInteracted) {
        userInteracted = true;
        
        if (readyToPlay && player && playerReady) {
            startBGM();
        }
    }
    hideBGMNotice();
}

function hideBGMNotice() {
    const notice = document.getElementById('bgm-notice');
    if (notice) {
        const noticeEl = notice.firstElementChild;
        noticeEl.style.opacity = '0';
        noticeEl.style.transform = 'translateY(20px)';
        setTimeout(() => {
            if (notice.parentNode) {
                notice.remove();
            }
        }, 300);
    }
}

function updateTrackInfo() {
    if (!playerReady || !true) return;

    const trackInfo = document.getElementById('track-info');
    if (!trackInfo) return;

    try {
        const videoData = player.getVideoData();

        if (videoData && videoData.title) {
            // XSS 방지를 위한 제목 정리
            const safeTitle = videoData.title.replace(/<[^>]*>/g, '');
            trackInfo.textContent = safeTitle;
        } else {
            if (playlistData.length > 1) {
                trackInfo.textContent = `곡 ${currentTrackIndex + 1}`;
            } else {
                trackInfo.textContent = '재생 중...';
            }
        }

        // 썸네일 업데이트
        updateThumbnail();
    } catch (error) {
        if (playlistData.length > 1) {
            trackInfo.textContent = `곡 ${currentTrackIndex + 1}`;
        } else {
            trackInfo.textContent = '재생 중...';
        }
    }
}

// 썸네일 업데이트 함수
function updateThumbnail() {
    if (!playerReady) return;

    try {
        const videoData = player.getVideoData();
        let videoId = null;

        if (videoData && videoData.video_id) {
            videoId = videoData.video_id;
        } else if (playlistData.length > 0 && playlistData[currentTrackIndex]) {
            videoId = playlistData[currentTrackIndex];
        }

        if (videoId) {
            const thumbnailUrl = `https://img.youtube.com/vi/${videoId}/mqdefault.jpg`;

            // CD 스킨 썸네일
            const cdThumb = document.getElementById('cd-thumbnail');
            if (cdThumb) {
                cdThumb.src = thumbnailUrl;
            }

            // Vinyl 스킨 썸네일
            const vinylThumb = document.getElementById('vinyl-thumbnail');
            if (vinylThumb) {
                vinylThumb.src = thumbnailUrl;
            }

            // Neon 스킨 썸네일
            const neonThumb = document.getElementById('neon-thumbnail');
            if (neonThumb) {
                neonThumb.src = thumbnailUrl;
            }

            // Pixel 스킨 썸네일
            const pixelThumb = document.getElementById('pixel-thumbnail');
            if (pixelThumb) {
                pixelThumb.src = thumbnailUrl;
            }
        }
    } catch (error) {
    }
}

function updateControls() {
    if (!playerReady || !true) return;
    
    try {
        const playlist = player.getPlaylist();
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        
        if (prevBtn && nextBtn) {
            if (playlist && playlist.length > 1) {
                prevBtn.disabled = false;
                nextBtn.disabled = false;
            } else {
                prevBtn.disabled = true;
                nextBtn.disabled = true;
            }
        }
    } catch (error) {
    }
}

// 보안 강화된 DOM 로드 이벤트
document.addEventListener('DOMContentLoaded', function() {
    // 볼륨 컨트롤 이벤트 (보안 강화)
    const volumeSlider = document.getElementById('volume-slider');
    if (volumeSlider) {
        volumeSlider.addEventListener('input', function() {
            // 볼륨 조절 시 보안 검증
            // 보안 체크 제거됨
            
            if (!userInteracted) {
                userInteracted = true;
                hideBGMNotice();
                if (readyToPlay && player && playerReady) {
                    startBGM();
                }
            }
            
            currentVolume = parseInt(this.value);

            if (playerReady && player) {
                player.setVolume(currentVolume);
            }

            updateVolumeIcon();
            saveBGMSettings();

            // Retro 스킨 볼륨 표시 업데이트
            const volDisplay = document.getElementById('vol-display');
            if (volDisplay) {
                volDisplay.textContent = currentVolume + '%';
            }

            // Pixel 스킨 볼륨 바 업데이트
            updatePixelVolumeBar(currentVolume);
        });
    }

    // 초기 픽셀 볼륨 바 설정
    updatePixelVolumeBar(currentVolume);

    // 외부 클릭 시 볼륨 팝업 닫기
    document.addEventListener('click', function(e) {
        const volumeControl = document.querySelector('.volume-control');
        if (volumeControl && !volumeControl.contains(e.target) && isVolumeOpen) {
            isVolumeOpen = false;
            const popup = document.getElementById('volume-popup');
            if (popup) popup.classList.remove('show');
        }
    });
    
    // 보안 YouTube API 로드
    if (typeof YT === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://www.youtube.com/iframe_api';
        script.onload = function() {
            // YouTube API 로드 성공
        };
        script.onerror = function() {
        };
        document.head.appendChild(script);
    }
});

// 페이지 언로드 시 정리
window.addEventListener('beforeunload', function() {
    if (player && typeof player.destroy === 'function') {
        try {
            player.destroy();
        } catch (error) {
        }
    }
});
