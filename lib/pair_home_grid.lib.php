<?php
if (!defined('_GNUBOARD_')) exit;
include_once __DIR__.'/pair_home.lib.php';

function pair_home_grid_default_layout() {
    return array(
        array('id'=>'grid-profile','type'=>'profile','x'=>0,'y'=>3,'w'=>5,'h'=>13,'data'=>array('name'=>'우리의 프로필','handle'=>'@our_home','bio'=>'두 사람의 이야기를 소개해 주세요.','src'=>'','link'=>'','link_label'=>'더 보기')),
        array('id'=>'grid-title','type'=>'text','x'=>5,'y'=>0,'w'=>25,'h'=>5,'data'=>array('text'=>'Our little home','font'=>'serif','font_size'=>46,'align'=>'center','color'=>'#657D8B','surface'=>'plain')),
        array('id'=>'grid-banner','type'=>'image','x'=>5,'y'=>5,'w'=>25,'h'=>8,'data'=>array('src'=>'','alt'=>'둘만의 장면을 담아 주세요','shape'=>'rounded','radius'=>8)),
        array('id'=>'grid-menu','type'=>'category','x'=>0,'y'=>0,'w'=>1,'h'=>3,'data'=>array('title'=>'PAGES','layout'=>'vertical','display'=>'icons','items'=>array(array('label'=>'PROFILE','url'=>'','icon'=>'user'),array('label'=>'DIARY','url'=>'','icon'=>'book'),array('label'=>'GALLERY','url'=>'','icon'=>'image')))),
        array('id'=>'grid-note','type'=>'text','x'=>5,'y'=>13,'w'=>10,'h'=>10,'data'=>array('text'=>"우리의 작은 기록\n\n좋아하는 장면과\n함께한 시간을 모아두는 곳.",'font'=>'sans','font_size'=>18,'align'=>'center','line_height'=>1.8,'color'=>'#74828B')),
        array('id'=>'grid-music','type'=>'bgm','x'=>15,'y'=>13,'w'=>15,'h'=>5,'data'=>array('title'=>'OUR PLAYLIST','artist'=>'','src'=>'','cover_src'=>'','player_style'=>'mini','loop'=>true,'volume'=>0.8)),
        array('id'=>'grid-date','type'=>'dday','x'=>15,'y'=>18,'w'=>15,'h'=>5,'data'=>array('title'=>'함께 쌓아가는 날들','date'=>date('Y-m-d'),'mode'=>'since','style'=>'plain'))
    );
}

function pair_home_grid_migrate_12_to_30($layout) {
    if (!is_array($layout)) return array();
    foreach ($layout as &$widget) {
        if (!is_array($widget) || ($widget['type'] ?? '') === 'sticker') continue;
        foreach (array(array('x','w'),array('y','h')) as $axis) {
            $start_key = $axis[0];
            $size_key = $axis[1];
            $old_start = pair_home_clean_number($widget[$start_key] ?? 0,0,12,0);
            $old_size = pair_home_clean_number($widget[$size_key] ?? 1,1,12,1);
            $old_end = min(12,$old_start+$old_size);
            $new_start = (int)round($old_start*2.5);
            $new_end = (int)round($old_end*2.5);
            $widget[$start_key] = $new_start;
            $widget[$size_key] = max(1,$new_end-$new_start);
        }
    }
    unset($widget);
    return $layout;
}

function pair_home_grid_sanitize_layout($layout) {
    if (!is_array($layout)) return array();
    $result = array();
    foreach (array_slice($layout, 0, 150) as $index=>$widget) {
        if (!is_array($widget)) continue;
        $is_profile = ($widget['type'] ?? '') === 'profile';
        $source = $widget;
        if ($is_profile) $source['type'] = 'image';
        $clean = pair_home_sanitize_layout(array($source));
        if (!$clean) continue;
        $clean = $clean[0];
        if ($clean['type'] === 'text') $clean['data']['surface'] = (($widget['data']['surface'] ?? '') === 'plain') ? 'plain' : 'card';
        if ($is_profile) {
            $data = is_array($widget['data'] ?? null) ? $widget['data'] : array();
            $clean['type'] = 'profile';
            $clean['data'] = array(
                'src'=>$clean['data']['src'], 'link'=>$clean['data']['link'],
                'header_src'=>pair_home_clean_url($data['header_src'] ?? ''),
                'portrait_zoom'=>pair_home_clean_number($data['portrait_zoom'] ?? 1,1,3,1),
                'portrait_focus_x'=>pair_home_clean_number($data['portrait_focus_x'] ?? 50,0,100,50),
                'portrait_focus_y'=>pair_home_clean_number($data['portrait_focus_y'] ?? 50,0,100,50),
                'header_zoom'=>pair_home_clean_number($data['header_zoom'] ?? 1,1,3,1),
                'header_focus_x'=>pair_home_clean_number($data['header_focus_x'] ?? 50,0,100,50),
                'header_focus_y'=>pair_home_clean_number($data['header_focus_y'] ?? 50,0,100,50),
                'handle'=>mb_substr(strip_tags((string)($data['handle'] ?? '')),0,80),
                'link_label'=>mb_substr(strip_tags((string)($data['link_label'] ?? '프로필 더 보기')),0,40),
                'name'=>mb_substr(strip_tags((string)($data['name'] ?? '프로필')),0,80),
                'bio'=>mb_substr(strip_tags((string)($data['bio'] ?? '')),0,1000)
            );
        }
        $data = is_array($widget['data'] ?? null) ? $widget['data'] : array();
        if ($clean['type'] !== 'sticker') {
            foreach (array('theme_color'=>'#A6C7D6','card_background'=>'#FFFFFF','card_text'=>'#657B86','gradient_start'=>'#A6C7D6','gradient_end'=>'#FFFFFF') as $key=>$fallback) {
                $color = strtoupper((string)($data[$key] ?? ($key === 'theme_color' ? ($data['accent'] ?? $fallback) : $fallback)));
                $clean['data'][$key] = preg_match('/^#[0-9A-F]{6}$/',$color) ? $color : $fallback;
            }
            $clean['data']['accent_source'] = ($data['accent_source'] ?? 'site') === 'custom' ? 'custom' : 'site';
            $legacy_text_source = isset($data['card_text']) && strtoupper((string)$data['card_text']) !== '#657B86' ? 'custom' : 'site';
            $clean['data']['text_color_source'] = ($data['text_color_source'] ?? $legacy_text_source) === 'custom' ? 'custom' : 'site';
            $clean['data']['fill_mode'] = ($data['fill_mode'] ?? 'solid') === 'gradient' ? 'gradient' : 'solid';
            $background_source = (string)($data['background_source'] ?? 'default');
            $clean['data']['background_source'] = in_array($background_source,array('default','site','custom'),true) ? $background_source : 'default';
            $clean['data']['gradient_start_source'] = ($data['gradient_start_source'] ?? 'site') === 'custom' ? 'custom' : 'site';
            $clean['data']['gradient_end_source'] = ($data['gradient_end_source'] ?? 'custom') === 'site' ? 'site' : 'custom';
            $theme = (string)($data['theme_preset'] ?? 'inherit');
            $clean['data']['theme_preset'] = in_array($theme,array('inherit','flat','line','bold','soft','pixel','glass'),true) ? $theme : 'inherit';
            $border_source = (string)($data['border_color_source'] ?? 'theme');
            $clean['data']['border_color_source'] = in_array($border_source,array('theme','site','custom'),true) ? $border_source : 'theme';
            $border_color = strtoupper((string)($data['border_color'] ?? '#7FAFD1'));
            $clean['data']['border_color'] = preg_match('/^#[0-9A-F]{6}$/',$border_color) ? $border_color : '#7FAFD1';
            $clean['data']['border_width'] = ($data['border_width'] ?? '') === '' ? '' : pair_home_clean_number($data['border_width'],0,8,1);
            $clean['data']['corner_radius'] = ($data['corner_radius'] ?? '') === '' ? '' : pair_home_clean_number($data['corner_radius'],0,48,8);
            $shadow = (string)($data['shadow_override'] ?? 'theme');
            $clean['data']['shadow_override'] = in_array($shadow,array('theme','on','off'),true) ? $shadow : 'theme';
        }
        if ($clean['type'] === 'category') {
            $clean['data']['display'] = in_array(($data['display'] ?? ''),array('text','labels'),true) ? 'text' : 'icons';
            $icons = array('home','user','book','image','heart','star','link');
            // Rebuild alongside the labels so skipped empty entries cannot shift icons.
            $clean['data']['items'] = array();
            foreach (array_slice(is_array($data['items'] ?? null) ? $data['items'] : array(),0,30) as $item) {
                if (!is_array($item)) continue;
                $label = mb_substr(strip_tags((string)($item['label'] ?? '')),0,60);
                if ($label === '') continue;
                $icon = (string)($item['icon'] ?? 'link');
                $clean['data']['items'][] = array('label'=>$label,'url'=>pair_home_clean_url($item['url'] ?? ''),'icon'=>in_array($icon,$icons,true) ? $icon : 'link');
            }
        }
        if ($clean['type'] !== 'sticker') {
            $w = (int)round(pair_home_clean_number($widget['w'] ?? 8,1,30,8));
            $h = (int)round(pair_home_clean_number($widget['h'] ?? 8,1,30,8));
            if ($clean['type'] === 'category') {
                $count = max(1,count($clean['data']['items'] ?? array()));
                if (($clean['data']['display'] ?? 'icons') === 'icons') {
                    $w = ($clean['data']['layout'] ?? 'vertical') === 'horizontal' ? min(30,$count) : 1;
                    $h = ($clean['data']['layout'] ?? 'vertical') === 'vertical' ? min(30,$count) : 1;
                } else {
                    $h = 1;
                }
            }
            $clean['w'] = $w;
            $clean['h'] = $h;
            $clean['x'] = min((int)round(pair_home_clean_number($widget['x'] ?? 0,0,30,0)),30-$w);
            $clean['y'] = min((int)round(pair_home_clean_number($widget['y'] ?? 0,0,30,0)),30-$h);
            $clean['rotation'] = 0;
        }
        $result[] = $clean;
    }
    return $result;
}

function pair_home_grid_layout_error($layout) {
    $ids = array();
    foreach ($layout as $i=>$a) {
        if ($a['id'] === '' || isset($ids[$a['id']])) return '블록 식별자가 중복되거나 비어 있습니다.';
        $ids[$a['id']] = true;
        if ($a['type'] === 'sticker') continue;
        foreach (array_slice($layout,0,$i) as $b) {
            if ($b['type'] === 'sticker') continue;
            if ($a['x'] < $b['x']+$b['w'] && $a['x']+$a['w'] > $b['x'] && $a['y'] < $b['y']+$b['h'] && $a['y']+$a['h'] > $b['y']) return '블록이 겹칩니다. 빈 칸으로 옮겨 주세요.';
        }
    }
    return '';
}
