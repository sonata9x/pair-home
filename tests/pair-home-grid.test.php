<?php
define('_GNUBOARD_',true);
function clean_xss_tags($value,$unused=0,$unused2=0) { return strip_tags($value); }
require __DIR__.'/../lib/pair_home_grid.lib.php';
function check_grid($value,$message) { if (!$value) throw new RuntimeException($message); }
$defaults = pair_home_grid_sanitize_layout(pair_home_grid_default_layout());
check_grid($defaults[3]['data']['layout'] === 'vertical' && $defaults[3]['w'] === 1 && $defaults[3]['h'] === 3,'default category is a vertical 1x3 icon column');
$default_left = min(array_map(function($widget){ return $widget['type'] === 'sticker' ? 30 : $widget['x']; },$defaults));
$default_right = max(array_map(function($widget){ return $widget['type'] === 'sticker' ? 0 : $widget['x']+$widget['w']; },$defaults));
check_grid($default_left === 4 && $default_right === 27,'default 23-column content is centered inside the 30-column canvas');
check_grid(count($defaults) === 7,'all starter blocks survive validation');
check_grid(pair_home_grid_layout_error($defaults) === '','starter blocks must not overlap');
check_grid($defaults[0]['type'] === 'profile','profile preserved');
check_grid($defaults[0]['w'] === 5,'starter profile uses five columns');
check_grid($defaults[1]['data']['surface'] === 'plain','transparent title preserved');
$again = pair_home_grid_sanitize_layout(json_decode(json_encode($defaults),true));
check_grid($again === $defaults,'save/load is stable');
$block = array('id'=>'a','type'=>'image','x'=>11.9,'y'=>-1,'w'=>3.4,'h'=>1,'rotation'=>27,'data'=>array('src'=>'https://example.com/image.png'));
$clean = pair_home_grid_sanitize_layout(array($block))[0];
check_grid($clean['x'] === 12 && $clean['y'] === 0 && $clean['w'] === 3 && $clean['h'] === 1 && $clean['rotation'] === 0,'grid cells and rotation enforced server side');
$legacy = array(
    array('id'=>'left','type'=>'text','x'=>0,'y'=>0,'w'=>1,'h'=>2,'data'=>array('text'=>'L')),
    array('id'=>'right','type'=>'text','x'=>1,'y'=>0,'w'=>1,'h'=>2,'data'=>array('text'=>'R'))
);
$migrated = pair_home_grid_sanitize_layout(pair_home_grid_migrate_12_to_30($legacy));
check_grid($migrated[0]['x']+$migrated[0]['w'] === $migrated[1]['x'],'legacy shared edges remain shared after migration');
check_grid(pair_home_grid_layout_error($migrated) === '','legacy grid migration does not create overlap');
$block['id']='b';
check_grid(pair_home_grid_layout_error(pair_home_grid_sanitize_layout(array($block,$block))) !== '','duplicate ids rejected');
$block['id']='c';$block['x']=0;$block['y']=0;
$overlap=$block;$overlap['id']='d';
check_grid(pair_home_grid_layout_error(pair_home_grid_sanitize_layout(array($block,$overlap))) !== '','overlapping blocks rejected');
$overlap['type']='sticker';$overlap['x']=.42;$overlap['rotation']=13;
$mixed=pair_home_grid_sanitize_layout(array($block,$overlap));
check_grid(pair_home_grid_layout_error($mixed) === '','stickers can overlap');
check_grid($mixed[1]['x'] === .42 && $mixed[1]['rotation'] === 13.0,'sticker movement and rotation stay free');
$profile = $defaults[0];
$profile['data']['header_src'] = 'https://example.com/header.png';
$profile['data']['src'] = 'https://example.com/avatar.png';
$profile['data']['handle'] = '@pair';
$profile['data']['theme_color'] = '#c290a8';
$profile['data']['card_background'] = '#FFF6FA';
$profile['data']['card_text'] = '#624957';
unset($profile['data']['text_color_source']);
$profile = pair_home_grid_sanitize_layout(array($profile))[0];
check_grid($profile['data']['header_src'] !== $profile['data']['src'],'header and portrait saved independently');
check_grid($profile['data']['theme_color'] === '#C290A8' && $profile['data']['card_background'] === '#FFF6FA' && $profile['data']['card_text'] === '#624957','custom palette preserved');
check_grid($profile['data']['text_color_source'] === 'custom','legacy custom text color is migrated without being overwritten');
check_grid(pair_home_grid_sanitize_layout(array($profile))[0] === $profile,'profile palette and images survive round trip');
$profile['data']['accent_source'] = 'custom';
$profile['data']['text_color_source'] = 'custom';
$profile['data']['fill_mode'] = 'gradient';
$profile['data']['gradient_start_source'] = 'site';
$profile['data']['gradient_end_source'] = 'custom';
$profile['data']['gradient_end'] = '#EAF7FF';
$profile = pair_home_grid_sanitize_layout(array($profile))[0];
check_grid($profile['data']['accent_source'] === 'custom' && $profile['data']['fill_mode'] === 'gradient','representative/custom color modes preserved');
check_grid($profile['data']['text_color_source'] === 'custom' && $profile['data']['card_text'] === '#624957','custom text and icon color mode preserved');
check_grid($profile['data']['gradient_start_source'] === 'site' && $profile['data']['gradient_end'] === '#EAF7FF','gradient endpoints can reference site color independently');
$profile['data']['header_src'] = 'javascript:alert(1)';
$profile['data']['theme_color'] = 'red;display:none';
$profile = pair_home_grid_sanitize_layout(array($profile))[0];
check_grid($profile['data']['header_src'] === '' && $profile['data']['theme_color'] === '#A6C7D6','invalid header URLs and theme values rejected');
$menu = $defaults[3];
$menu['data']['layout'] = 'vertical';$menu['data']['display'] = 'text';
$menu['data']['items'] = array(array('label'=>'','url'=>'','icon'=>'home'),array('label'=>'기록','url'=>'/diary','icon'=>'book'));
$menu = pair_home_grid_sanitize_layout(array($menu))[0];
check_grid($menu['data']['display'] === 'text' && $menu['data']['items'][0]['icon'] === 'book' && $menu['h'] === 1,'text category stays borderless and horizontal-sized');
$menu['data']['display'] = 'icons';$menu['data']['layout'] = 'vertical';
$menu = pair_home_grid_sanitize_layout(array($menu))[0];
check_grid($menu['w'] === 1 && $menu['h'] === count($menu['data']['items']),'icon category assigns one grid cell per item');
$text = $defaults[1];
$text['data']['font'] = 'gowun-dodum';
$text = pair_home_grid_sanitize_layout(array($text))[0];
check_grid($text['data']['font'] === 'gowun-dodum','webfont selection survives validation');
$theme_widget = array('id'=>'theme','type'=>'label','x'=>0,'y'=>10,'w'=>2,'h'=>1,'data'=>array('text'=>'LATEST','theme_preset'=>'pixel','border_color_source'=>'custom','border_color'=>'#334455','border_width'=>3,'corner_radius'=>0,'shadow_override'=>'off'));
$theme_widget = pair_home_grid_sanitize_layout(array($theme_widget))[0];
check_grid($theme_widget['data']['theme_preset'] === 'pixel' && $theme_widget['data']['border_color'] === '#334455','theme preset and border override preserved');
check_grid((float)$theme_widget['data']['border_width'] === 3.0 && (float)$theme_widget['data']['corner_radius'] === 0.0 && $theme_widget['data']['shadow_override'] === 'off','numeric theme overrides preserved');
$theme_widget['data']['theme_preset'] = 'bold';
$theme_widget = pair_home_grid_sanitize_layout(array($theme_widget))[0];
check_grid($theme_widget['data']['theme_preset'] === 'bold','bold line theme survives validation');
$bgm = pair_home_grid_sanitize_layout(array(array('id'=>'bgm','type'=>'bgm','x'=>0,'y'=>20,'w'=>10,'h'=>4,'data'=>array('title'=>'TRACK','artist'=>'PAIR','src'=>'https://example.com/song.mp3','cover_src'=>'https://example.com/cover.jpg','cover_zoom'=>1.75,'cover_focus_x'=>28,'cover_focus_y'=>64,'player_style'=>'lp','loop'=>true,'volume'=>0.65))))[0];
check_grid($bgm['data']['player_style'] === 'lp' && $bgm['data']['artist'] === 'PAIR','BGM player style and artist survive validation');
check_grid($bgm['data']['cover_src'] === 'https://example.com/cover.jpg' && (float)$bgm['data']['volume'] === 0.65,'BGM cover and volume survive validation');
check_grid((float)$bgm['data']['cover_zoom'] === 1.75 && (float)$bgm['data']['cover_focus_x'] === 28.0 && (float)$bgm['data']['cover_focus_y'] === 64.0,'BGM cover crop survives validation');
$pixel_bgm = pair_home_grid_sanitize_layout(array(array('id'=>'pixel-bgm','type'=>'bgm','x'=>10,'y'=>20,'w'=>9,'h'=>9,'data'=>array('player_style'=>'lp-pixel'))))[0];
check_grid($pixel_bgm['data']['player_style'] === 'lp-pixel','pixel LP player style survives validation');
$pixel_sleeve = pair_home_grid_sanitize_layout(array(array('id'=>'pixel-sleeve','type'=>'bgm','x'=>19,'y'=>20,'w'=>10,'h'=>7,'data'=>array('player_style'=>'sleeve-pixel'))))[0];
check_grid($pixel_sleeve['data']['player_style'] === 'sleeve-pixel','pixel sleeve player style survives validation');
$free_frame = pair_home_grid_sanitize_layout(array(array('id'=>'free-frame','type'=>'image','x'=>0,'y'=>0,'w'=>7,'h'=>5,'data'=>array('ratio'=>'free'))))[0];
$ratio_frame = pair_home_grid_sanitize_layout(array(array('id'=>'ratio-frame','type'=>'image','x'=>8,'y'=>0,'w'=>8,'h'=>6,'data'=>array('ratio'=>'4:3'))))[0];
check_grid($free_frame['data']['ratio'] === 'free' && $ratio_frame['data']['ratio'] === '4:3','free and fixed image ratios survive validation');
$profile_crop = pair_home_grid_sanitize_layout(array(array('id'=>'profile-crop','type'=>'profile','x'=>0,'y'=>0,'w'=>5,'h'=>13,'data'=>array('portrait_zoom'=>1.5,'portrait_focus_x'=>25,'portrait_focus_y'=>70,'header_zoom'=>2,'header_focus_x'=>80,'header_focus_y'=>20))))[0];
check_grid((float)$profile_crop['data']['portrait_zoom'] === 1.5 && (float)$profile_crop['data']['portrait_focus_x'] === 25.0 && (float)$profile_crop['data']['header_zoom'] === 2.0 && (float)$profile_crop['data']['header_focus_y'] === 20.0,'profile image crop settings survive validation');
$extras = array(
    array('id'=>'separator','type'=>'separator','x'=>0,'y'=>0,'w'=>4,'h'=>1,'data'=>array('orientation'=>'horizontal','line_style'=>'dotted','thickness'=>2)),
    array('id'=>'banner','type'=>'linkbanner','x'=>4,'y'=>0,'w'=>3,'h'=>1,'data'=>array('title'=>'FRIEND','link'=>'https://example.com','image_fit'=>'cover','show_title'=>true,'new_tab'=>true)),
    array('id'=>'calendar','type'=>'calendar','x'=>7,'y'=>0,'w'=>4,'h'=>4,'data'=>array('title'=>'CALENDAR','calendar_mode'=>'fixed','year'=>2026,'month'=>9,'highlights'=>'1,12,30','events'=>"2026-09-12|기념일\n2026-09-30|업데이트"))
);
$extras = pair_home_grid_sanitize_layout($extras);
check_grid(count($extras) === 3 && $extras[0]['data']['line_style'] === 'dotted','separator survives validation');
check_grid($extras[1]['data']['new_tab'] === true && $extras[1]['data']['link'] !== '','link banner survives validation');
check_grid($extras[1]['data']['image_fit'] === 'cover' && $extras[1]['data']['show_title'] === true,'link banner image options survive validation');
check_grid($extras[2]['data']['year'] === 2026 && $extras[2]['data']['month'] === 9,'calendar settings survive validation');
check_grid(strpos($extras[2]['data']['events'],'2026-09-12|기념일') !== false,'calendar events survive validation');
$frame_layout = pair_home_grid_sanitize_layout(array(
    array('id'=>'webframe','type'=>'webframe','x'=>2,'y'=>2,'w'=>20,'h'=>15,'data'=>array('show_address'=>false,'address'=>'pair.example','body_style'=>'transparent','content_mode'=>'text','body_text'=>'memo','body_font'=>'gowun-dodum','body_font_size'=>18,'body_align'=>'center','theme_preset'=>'pixel')),
    array('id'=>'inside','type'=>'text','x'=>5,'y'=>6,'w'=>6,'h'=>4,'data'=>array('text'=>'inside'))
));
check_grid(count($frame_layout) === 2 && $frame_layout[0]['data']['show_address'] === false && $frame_layout[0]['data']['body_style'] === 'transparent','web frame options survive validation');
check_grid($frame_layout[0]['data']['content_mode'] === 'text' && $frame_layout[0]['data']['body_text'] === 'memo' && $frame_layout[0]['data']['body_font'] === 'gowun-dodum','web frame memo settings survive validation');
check_grid(pair_home_grid_layout_error($frame_layout) !== '','web frames cannot overlap regular widgets');
$second_frame = $frame_layout[0];$second_frame['id']='webframe-2';$second_frame['x']=3;
check_grid(pair_home_grid_layout_error(array($frame_layout[0],$second_frame)) !== '','web frames cannot overlap each other');
if (in_array('--fixture',$argv,true)) echo json_encode($defaults,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
else echo "PHP grid validation and round-trip checks passed\n";
