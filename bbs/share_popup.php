<?php
/**
 * RA0 Edition - 게시글 공유 팝업
 *
 * 짧은 URL 생성 및 공유 UI
 */

include_once './_common.php';

$bo_table = isset($_GET['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['bo_table']) : '';
$wr_id = isset($_GET['wr_id']) ? (int)$_GET['wr_id'] : 0;

if (!$bo_table || !$wr_id) {
    alert('잘못된 접근입니다.');
}

// 게시글 존재 확인
$write_table = $g5['write_prefix'] . $bo_table;
$write = get_write($write_table, $wr_id);

if (!$write['wr_id']) {
    alert('존재하지 않는 게시글입니다.');
}

// 짧은 URL 생성/조회
$short_key = '';
$short_url = '';
$original_url = G5_BBS_URL . '/board.php?bo_table=' . $bo_table . '&wr_id=' . $wr_id;

if (function_exists('get_short_url')) {
    $short_key = get_short_url($bo_table, $wr_id);
    if ($short_key) {
        $short_url = G5_URL . '/s/' . $short_key;
    }
}

if (!$short_url) {
    alert('짧은 URL 생성에 실패했습니다.\n\n관리자 페이지에서 URL 단축 시스템을 설치해주세요.');
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>게시글 공유</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding: 30px;
            background: var(--card-bg-color, #fff);
            color: var(--text-color, #333);
        }
        .share-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .share-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .share-header h1 {
            font-size: 20px;
            margin-bottom: 8px;
        }
        .share-header p {
            font-size: 14px;
            color: #666;
        }
        .url-box {
            background: var(--hover-bg, #f8f9fa);
            border: 1px solid var(--border-color, #ddd);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .url-label {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .url-display {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            border: 1px solid var(--border-color, #ddd);
            border-radius: 6px;
            padding: 12px 15px;
        }
        .url-text {
            flex: 1;
            font-family: monospace;
            font-size: 14px;
            word-break: break-all;
            color: var(--primary-color, #007bff);
        }
        .btn-copy {
            padding: 8px 16px;
            background: var(--primary-color, #007bff);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
            transition: opacity 0.2s;
        }
        .btn-copy:hover {
            opacity: 0.8;
        }
        .btn-copy:active {
            transform: scale(0.98);
        }
        .btn-copy.copied {
            background: #28a745;
        }
        .info-text {
            font-size: 12px;
            color: #666;
            text-align: center;
            margin-top: 20px;
            line-height: 1.5;
        }
        .btn-close {
            display: block;
            width: 100%;
            padding: 12px;
            background: var(--border-color, #ddd);
            color: #333;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 20px;
        }
        .btn-close:hover {
            background: #ccc;
        }
    </style>
</head>
<body>
    <div class="share-container">
        <div class="share-header">
            <h1>게시글 공유</h1>
            <p>아래 URL를 복사하여 공유하세요</p>
        </div>

        <!-- URL -->
        <div class="url-box">
            <div class="url-label">단축 URL</div>
            <div class="url-display">
                <div class="url-text" id="short-url"><?php echo htmlspecialchars($short_url); ?></div>
                <button type="button" class="btn-copy" onclick="copyUrl('short-url', this)">
                    복사
                </button>
            </div>
        </div>

        <!-- 원본 URL (참고용) -->
        <div class="url-box">
            <div class="url-label">원본 URL</div>
            <div class="url-display">
                <div class="url-text" id="original-url" style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($original_url); ?></div>
                <button type="button" class="btn-copy" onclick="copyUrl('original-url', this)">
                    복사
                </button>
            </div>
        </div>

        <div class="info-text">
            URL을 SNS, 메신저 등에서 간편하게 공유할 수 있습니다.<br>
            링크를 클릭하면 이 게시글로 자동 이동합니다.
        </div>

        <button type="button" class="btn-close" onclick="window.close()">닫기</button>
    </div>

    <script>
    function copyUrl(elementId, button) {
        const element = document.getElementById(elementId);
        const text = element.textContent;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                showCopied(button);
            }, function() {
                fallbackCopy(text, button);
            });
        } else {
            fallbackCopy(text, button);
        }
    }

    function fallbackCopy(text, button) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = 0;
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showCopied(button);
        } catch (err) {
            alert('복사 실패. 수동으로 복사해주세요.');
        }
        document.body.removeChild(textarea);
    }

    function showCopied(button) {
        const originalText = button.textContent;
        button.textContent = '✓ 복사됨';
        button.classList.add('copied');

        setTimeout(function() {
            button.textContent = originalText;
            button.classList.remove('copied');
        }, 2000);
    }
    </script>
</body>
</html>
