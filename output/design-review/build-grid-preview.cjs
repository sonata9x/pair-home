const fs=require('fs');
const path=require('path');
const {execFileSync}=require('child_process');
const root=path.resolve(__dirname,'../..');
const main=fs.readFileSync(path.join(root,'main.php'),'utf8');
const markup=main.slice(main.indexOf('<div class="pair-home-shell'),main.indexOf('<script type="application/json"')).replace(/<\?php[\s\S]*?\?>/g,'').replace(/^[ \t]+$/gm,'');
const widgets=JSON.parse(execFileSync(process.env.PHP_BINARY||'php',[path.join(root,'tests/pair-home-grid.test.php'),'--fixture'],{encoding:'utf8'}));
const styles=['default.css.php','ra0-content.css','pair-home.css','pair-home-editor.css','pair-home-grid.css'].map(file=>'<link rel="stylesheet" href="../../css/'+file+'">').join('');
const fonts='<link rel="stylesheet" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/galmuri/dist/galmuri.css"><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Gowun+Batang:wght@400;700&family=Gowun+Dodum&family=IBM+Plex+Sans+KR:wght@300;400;500;600;700&family=Nanum+Pen+Script&family=Noto+Sans+KR:wght@300;400;500;600;700&family=Noto+Serif+KR:wght@400;500;600;700&display=swap">';
function page(title,previewWidgets){
  const config={admin:true,preview:true,layoutMode:'grid',backgroundColor:'#E8F0F4',backgroundImage:'',representativeColor:'#7FAFD1',defaultTheme:'flat',boards:[{id:'diary',label:'DIARY',url:'/bbs/board.php?bo_table=diary'},{id:'gallery',label:'GALLERY',url:'/bbs/board.php?bo_table=gallery'},{id:'guest',label:'GUEST',url:'/bbs/board.php?bo_table=guest'}],widgets:previewWidgets};
  return '<!doctype html><html lang="ko"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'+title+'</title>'+fonts+styles+'<body>'+markup+'<script type="application/json" id="pair-home-bootstrap">'+JSON.stringify(config)+'</script><script src="../../js/pair-home-grid-layout.js"></script><script src="../../js/pair-home-grid.js"></script></body></html>';
}
fs.writeFileSync(path.join(__dirname,'grid-preview.html'),page('격자형 페어홈 · 테스트 배치',widgets));
const common={accent_source:'site',fill_mode:'solid',background_source:'default',gradient_start_source:'site',gradient_end_source:'custom',gradient_end:'#FFFFFF'};
function testToneDataUri(frequency=220,duration=.8){
  const rate=8000,samples=Math.floor(rate*duration),buffer=Buffer.alloc(44+samples*2);
  buffer.write('RIFF',0);buffer.writeUInt32LE(36+samples*2,4);buffer.write('WAVE',8);buffer.write('fmt ',12);buffer.writeUInt32LE(16,16);buffer.writeUInt16LE(1,20);buffer.writeUInt16LE(1,22);buffer.writeUInt32LE(rate,24);buffer.writeUInt32LE(rate*2,28);buffer.writeUInt16LE(2,32);buffer.writeUInt16LE(16,34);buffer.write('data',36);buffer.writeUInt32LE(samples*2,40);
  for(let i=0;i<samples;i++)buffer.writeInt16LE(Math.round(Math.sin(i*2*Math.PI*frequency/rate)*900),44+i*2);
  return 'data:audio/wav;base64,'+buffer.toString('base64');
}
const webframeWidgets=[
  {id:'flat-frame',type:'webframe',x:1,y:1,w:8,h:7,z:1,locked:false,data:{...common,show_address:true,address:'flat.home',body_style:'panel',content_mode:'text',body_text:'FLAT',body_font:'pretendard',body_font_size:16,body_align:'center',theme_preset:'flat'}},
  {id:'line-frame',type:'webframe',x:11,y:1,w:8,h:7,z:2,locked:false,data:{...common,show_address:true,address:'line.home',body_style:'panel',content_mode:'empty',theme_preset:'line'}},
  {id:'bold-frame',type:'webframe',x:21,y:1,w:8,h:7,z:3,locked:false,data:{...common,show_address:true,address:'bold.home',body_style:'panel',content_mode:'text',body_text:'BOLD LINE',body_font:'pretendard',body_font_size:16,body_align:'center',theme_preset:'bold'}},
  {id:'soft-frame',type:'webframe',x:1,y:10,w:13,h:7,z:4,locked:false,data:{...common,show_address:true,address:'soft.home',body_style:'panel',content_mode:'text',body_text:'SOFT MEMO',body_font:'pretendard',body_font_size:18,body_align:'center',theme_preset:'soft'}},
  {id:'pixel-frame',type:'webframe',x:16,y:10,w:13,h:7,z:5,locked:false,data:{...common,show_address:true,address:'PIXEL.EXE',body_style:'panel',content_mode:'text',body_text:'PIXEL MEMO',body_font:'pretendard',body_font_size:16,body_align:'center',theme_preset:'pixel'}},
  {id:'glass-frame',type:'webframe',x:2,y:20,w:26,h:8,z:6,locked:false,data:{...common,show_address:true,address:'archive://our-days',body_style:'panel',content_mode:'image',src:'',theme_preset:'glass'}}
];
fs.writeFileSync(path.join(__dirname,'webframe-preview.html'),page('홈페이지 프레임 · 테마 미리보기',webframeWidgets));
const themeWidgets=[
  {id:'soft-profile',type:'profile',x:0,y:0,w:5,h:14,z:1,locked:false,data:{...common,name:'PAIR HOME',handle:'@our_archive',bio:'둘만의 기록을 모아두는 작은 공간',src:'',header_src:'',link:'',link_label:'PROFILE',theme_preset:'soft'}},
  {id:'line-category',type:'category',x:7,y:0,w:3,h:1,z:2,locked:false,data:{...common,title:'PAGES',layout:'horizontal',display:'icons',items:[{label:'HOME',url:'#',icon:'home'},{label:'DIARY',url:'#',icon:'book'},{label:'GALLERY',url:'#',icon:'image'}],theme_preset:'line'}},
  {id:'text-category',type:'category',x:7,y:6,w:10,h:1,z:3,locked:false,data:{...common,title:'BOARDS',layout:'horizontal',display:'text',items:[{label:'HOME',url:'#',icon:'home'},{label:'DIARY',url:'#',icon:'book'},{label:'GALLERY',url:'#',icon:'image'}],theme_preset:'bold'}},
  {id:'glass-calendar',type:'calendar',x:14,y:12,w:9,h:11,z:4,locked:false,data:{...common,title:'CALENDAR',calendar_mode:'fixed',year:2026,month:10,highlights:'1,12,24',events:'2026-10-12|기념일\n2026-10-24|업데이트',theme_preset:'glass'}},
  {id:'bold-dday',type:'dday',x:24,y:0,w:6,h:5,z:4,locked:false,data:{...common,title:'OUR DAYS',date:'2025-10-01',mode:'since',include_today:true,show_date:true,style:'plain',theme_preset:'bold'}},
  {id:'pixel-bgm',type:'bgm',x:24,y:6,w:6,h:5,z:5,locked:false,data:{...common,title:'NOW PLAYING',artist:'PAIR RADIO',src:'',cover_src:'',player_style:'deck',loop:true,volume:.8,theme_preset:'pixel'}},
  {id:'flat-label',type:'label',x:7,y:9,w:7,h:3,z:6,locked:false,data:{...common,text:'LATEST UPDATE',shape:'rectangle',align:'center',theme_preset:'flat'}},
  {id:'bold-banner',type:'linkbanner',x:15,y:8,w:8,h:3,z:7,locked:false,data:{...common,title:'FRIENDS',src:'',link:'#',new_tab:false,theme_preset:'bold'}},
  {id:'pixel-text',type:'text',x:0,y:16,w:12,h:6,z:8,locked:false,data:{...common,text:'PIXEL NOTE',surface:'card',font:'pretendard',font_size:28,color:'#657B86',weight:'bold',align:'center',line_height:1.2,theme_preset:'pixel'}},
  {id:'glass-label',type:'label',x:0,y:24,w:8,h:3,z:9,locked:false,data:{...common,text:'GLASS LABEL',shape:'pill',align:'center',theme_preset:'glass'}}
];
fs.writeFileSync(path.join(__dirname,'widget-theme-preview.html'),page('전체 위젯 · 테마 일괄 적용 미리보기',themeWidgets));
const bgmPreviewWidgets=[
  {id:'bgm-mini',type:'bgm',x:0,y:0,w:12,h:3,z:1,locked:false,data:{...common,title:'MINI BAR',artist:'PAIR RADIO',src:'',cover_src:'',player_style:'mini',loop:true,volume:.8,theme_preset:'flat'}},
  {id:'bgm-cover',type:'bgm',x:13,y:0,w:9,h:5,z:2,locked:false,data:{...common,title:'COVER PLAYER',artist:'OUR ARCHIVE',src:'',cover_src:'',player_style:'cover',loop:true,volume:.8,theme_preset:'line'}},
  {id:'bgm-album',type:'bgm',x:22,y:0,w:8,h:9,z:3,locked:false,data:{...common,title:'ALBUM CARD',artist:'A + B',src:'',cover_src:'',player_style:'album',loop:true,volume:.8,theme_preset:'soft'}},
  {id:'bgm-lp',type:'bgm',x:0,y:5,w:10,h:10,z:4,locked:false,data:{...common,title:'VINYL PLAYER',artist:'SIDE A',src:'',cover_src:'',player_style:'lp',loop:true,volume:.8,theme_preset:'bold'}},
  {id:'bgm-sleeve',type:'bgm',x:11,y:8,w:10,h:7,z:5,locked:false,data:{...common,title:'JACKET + LP',artist:'PAIR HOME',src:'',cover_src:'',player_style:'sleeve',loop:true,volume:.8,theme_preset:'flat'}},
  {id:'bgm-backdrop',type:'bgm',x:0,y:17,w:14,h:8,z:6,locked:false,data:{...common,title:'BLURRED COVER',artist:'MEMORY TRACK',src:'',cover_src:'',player_style:'backdrop',loop:true,volume:.8,theme_preset:'glass'}},
  {id:'bgm-deck',type:'bgm',x:15,y:17,w:15,h:4,z:7,locked:false,data:{...common,title:'RETRO AUDIO DECK',artist:'00:00 READY',src:'',cover_src:'',player_style:'deck',loop:true,volume:.8,theme_preset:'pixel'}},
  {id:'bgm-lp-pixel',type:'bgm',x:21,y:21,w:9,h:9,z:8,locked:false,data:{...common,title:'PIXEL VINYL',artist:'8-BIT SIDE A',src:'',cover_src:'',player_style:'lp-pixel',loop:true,volume:.8,theme_preset:'pixel'}}
];
fs.writeFileSync(path.join(__dirname,'bgm-widget-preview.html'),page('BGM 위젯 · 8가지 플레이어',bgmPreviewWidgets));
const functionalToneA=testToneDataUri(220,2),functionalToneB=testToneDataUri(330,2);
const bgmFunctionalWidgets=[
  {id:'functional-a',type:'bgm',x:2,y:3,w:12,h:5,z:1,locked:false,data:{...common,title:'PLAY TEST A',artist:'220 HZ',src:functionalToneA,cover_src:'',player_style:'cover',loop:true,volume:.08,theme_preset:'line'}},
  {id:'functional-b',type:'bgm',x:16,y:3,w:12,h:5,z:2,locked:false,data:{...common,title:'PLAY TEST B',artist:'330 HZ',src:functionalToneB,cover_src:'',player_style:'cover-pixel',loop:true,volume:.08,theme_preset:'pixel'}}
];
fs.writeFileSync(path.join(__dirname,'bgm-functional-preview.html'),page('BGM 위젯 · 실제 재생 전환 테스트',bgmFunctionalWidgets));
const lpPreviewWidgets=[
  {id:'lp-standard',type:'bgm',x:2,y:2,w:10,h:10,z:1,locked:false,data:{...common,title:'VINYL PLAYER',artist:'SIDE A',src:'',cover_src:'',player_style:'lp',loop:true,volume:.8,theme_preset:'bold'}},
  {id:'lp-pixel',type:'bgm',x:17,y:2,w:10,h:10,z:2,locked:false,data:{...common,title:'PIXEL VINYL',artist:'8-BIT SIDE A',src:'',cover_src:'',player_style:'lp-pixel',loop:true,volume:.8,theme_preset:'pixel'}}
];
fs.writeFileSync(path.join(__dirname,'bgm-lp-preview.html'),page('LP 플레이어 · 일반과 도트',lpPreviewWidgets));
const bgmSpacingWidgets=[
  {id:'spacing-sleeve',type:'bgm',x:1,y:2,w:11,h:7,z:1,locked:false,data:{...common,title:'JACKET + LP',artist:'PAIR HOME',src:'',cover_src:'',player_style:'sleeve',loop:true,volume:.8,theme_preset:'flat'}},
  {id:'spacing-deck',type:'bgm',x:14,y:2,w:15,h:4,z:2,locked:false,data:{...common,title:'RETRO AUDIO DECK',artist:'00:00 READY',src:'',cover_src:'',player_style:'deck',loop:true,volume:.8,theme_preset:'pixel'}}
];
fs.writeFileSync(path.join(__dirname,'bgm-spacing-preview.html'),page('BGM 위젯 · 재킷과 레트로 간격',bgmSpacingWidgets));
const bgmPixelWidgets=[
  {id:'pixel-mini',type:'bgm',x:0,y:0,w:12,h:3,z:1,locked:false,data:{...common,title:'PIXEL MINI',artist:'8-BIT RADIO',src:'',cover_src:'',player_style:'mini-pixel',loop:true,volume:.8,theme_preset:'pixel'}},
  {id:'pixel-cover',type:'bgm',x:13,y:0,w:9,h:5,z:2,locked:false,data:{...common,title:'PIXEL COVER',artist:'OUR ARCHIVE',src:'',cover_src:'',player_style:'cover-pixel',loop:true,volume:.8,theme_preset:'pixel'}},
  {id:'pixel-album',type:'bgm',x:22,y:0,w:8,h:9,z:3,locked:false,data:{...common,title:'PIXEL ALBUM',artist:'A + B',src:'',cover_src:'',player_style:'album-pixel',loop:true,volume:.8,theme_preset:'pixel'}},
  {id:'pixel-lp',type:'bgm',x:0,y:5,w:10,h:10,z:4,locked:false,data:{...common,title:'PIXEL VINYL',artist:'8-BIT SIDE A',src:'',cover_src:'',player_style:'lp-pixel',loop:true,volume:.8,theme_preset:'pixel'}},
  {id:'pixel-sleeve',type:'bgm',x:11,y:8,w:10,h:7,z:5,locked:false,data:{...common,title:'PIXEL JACKET',artist:'PAIR HOME',src:'',cover_src:'',player_style:'sleeve-pixel',loop:true,volume:.8,theme_preset:'pixel'}},
  {id:'pixel-backdrop',type:'bgm',x:0,y:17,w:14,h:8,z:6,locked:false,data:{...common,title:'PIXEL BACKDROP',artist:'MEMORY TRACK',src:'',cover_src:'',player_style:'backdrop-pixel',loop:true,volume:.8,theme_preset:'pixel'}},
  {id:'pixel-deck',type:'bgm',x:15,y:17,w:15,h:4,z:7,locked:false,data:{...common,title:'RETRO AUDIO DECK',artist:'00:00 READY',src:'',cover_src:'',player_style:'deck',loop:true,volume:.8,theme_preset:'pixel'}}
];
fs.writeFileSync(path.join(__dirname,'bgm-pixel-preview.html'),page('BGM 위젯 · 전체 도트 버전',bgmPixelWidgets));
