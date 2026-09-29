<?php
$sub_menu = "200100";
require_once './_common.php';

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.", G5_URL);

$g5['title'] = '파일 관리';
require_once './admin.head.php';
?>

<style>
.file_manager_tabs {
    display: flex;
    border-bottom: 2px solid #ddd;
    margin-bottom: 20px;
    background: #f8f9fa;
}

.file_tab {
    padding: 12px 20px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-bottom: none;
    cursor: pointer;
    margin-right: 2px;
    transition: all 0.3s ease;
}

.file_tab:hover {
    background: #e9ecef;
}

.file_tab.active {
    background: #fff;
    border-top: 3px solid #007bff;
    font-weight: bold;
    color: #007bff;
}

.file_content {
    display: none;
}

.file_content.active {
    display: block;
}

.file_content iframe {
    width: 100%;
    height: 600px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<!-- 탭 메뉴 -->
<div class="file_manager_tabs">
    <div class="file_tab active" data-tab="session">세션 파일</div>
    <div class="file_tab" data-tab="cache">캐시 파일</div>
    <div class="file_tab" data-tab="thumbnail">썸네일 파일</div>
</div>

<!-- 세션 파일 탭 -->
<div id="tab_session" class="file_content active">
    <div class="local_desc02 local_desc">
        <p>
            완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
        </p>
    </div>

        <?php
        flush();

        $list_tag_st = "";
        $list_tag_end = "";
        if (!$dir=@opendir(G5_DATA_PATH.'/session')) {
        echo "<p>세션 디렉토리를 열지못했습니다.</p>";
        } else {
            $list_tag_st = "<ul class=\"session_del\">\n<li>완료됨</li>\n";
            $list_tag_end = "</ul>\n";
        }

        $cnt=0;
        echo $list_tag_st;
        while($file=readdir($dir)) {

            if (!strstr($file,'sess_')) continue;
            if (strpos($file,'sess_')!=0) continue;

            $session_file = G5_DATA_PATH.'/session/'.$file;

            if (!$atime=@fileatime($session_file)) {
                continue;
            }
            if (time() > $atime + (3600 * 6)) {  // 지난시간을 초로 계산해서 적어주시면 됩니다. default : 6시간전
                $cnt++;
                $return = unlink($session_file);
                //echo "<script>document.getElementById('ct').innerHTML += '{$session_file}<br/>';</script>\n";
                echo "<li>{$session_file}</li>\n";

                flush();

                if ($cnt%10==0)
                    //echo "<script>document.getElementById('ct').innerHTML = '';</script>\n";
                    echo "\n";
            }
        }
        echo $list_tag_end;
        echo '<div class="local_desc01 local_desc"><p><strong>세션데이터 '.$cnt.'건 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>'.PHP_EOL;
    ?>
</div>

<!-- 캐시 파일 탭 -->
<div id="tab_cache" class="file_content">
    <?php
    @require_once './safe_check.php';
    if (function_exists('social_log_file_delete')) {
        social_log_file_delete();
    }

    run_event('adm_cache_file_delete_before');

    $g5['title'] = '캐시파일 일괄삭제';
    require_once './admin.head.php';
    ?>

    <div class="local_desc02 local_desc">
        <p>
            완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
        </p>
    </div>

    <?php
    flush();

    if (!$dir = @opendir(G5_DATA_PATH . '/cache')) {
        echo '<p>캐시디렉토리를 열지못했습니다.</p>';
    }

    $cnt = 0;
    echo '<ul class="session_del">' . PHP_EOL;

    $files = glob(G5_DATA_PATH . '/cache/latest-*');
    $content_files = glob(G5_DATA_PATH . '/cache/content-*');

    $files = array_merge($files, $content_files);
    if (is_array($files)) {
        foreach ($files as $cache_file) {
            $cnt++;
            unlink($cache_file);
            echo '<li>' . $cache_file . '</li>' . PHP_EOL;

            flush();

            if ($cnt % 10 == 0) {
                echo PHP_EOL;
            }
        }
    }

    run_event('adm_cache_file_delete');

    echo '<li>완료됨</li></ul>' . PHP_EOL;
    echo '<div class="local_desc01 local_desc"><p><strong>최신글 캐시파일 ' . $cnt . '건 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>' . PHP_EOL;
    ?>
</div>

<!-- 썸네일 파일 탭 -->
<div id="tab_thumbnail" class="file_content">
    <div class="local_desc02 local_desc">
        <p>
            완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
        </p>
    </div>

    <?php
    $directory = array();
    $dl = array('file', 'editor');

    if (defined('G5_USE_SHOP') && G5_USE_SHOP) {
        $dl[] = 'item';
    }

    foreach($dl as $val) {
        if($handle = opendir(G5_DATA_PATH.'/'.$val)) {
            while(false !== ($entry = readdir($handle))) {
                if($entry == '.' || $entry == '..')
                    continue;

                $path = G5_DATA_PATH.'/'.$val.'/'.$entry;

                if(is_dir($path))
                    $directory[] = $path;
            }
        }
    }

    flush();

    if (empty($directory)) {
        echo '<p>썸네일디렉토리를 열지못했습니다.</p>';
    }

    $cnt=0;
    echo '<ul>'.PHP_EOL;

    foreach($directory as $dir) {
        $files = glob($dir.'/thumb-*');
        if (is_array($files)) {
            foreach($files as $thumbnail) {
                $cnt++;
                @unlink($thumbnail);

                echo '<li>'.$thumbnail.'</li>'.PHP_EOL;

                flush();

                if ($cnt%10==0)
                    echo PHP_EOL;
            }
        }
    }

    echo '<li>완료됨</li></ul>'.PHP_EOL;
    echo '<div class="local_desc01 local_desc"><p><strong>썸네일 '.$cnt.'건의 삭제 완료됐습니다.</strong><br>프로그램의 실행을 끝마치셔도 좋습니다.</p></div>'.PHP_EOL;
    ?>
</div>

<script>
// 탭 기능
$(document).ready(function() {
    $('.file_tab').click(function() {
        var tab = $(this).data('tab');
        
        // 탭 활성화
        $('.file_tab').removeClass('active');
        $(this).addClass('active');
        
        // 컨텐츠 표시
        $('.file_content').removeClass('active');
        $('#tab_' + tab).addClass('active');
    });
});
</script>

<?php
require_once './admin.tail.php';
?>
