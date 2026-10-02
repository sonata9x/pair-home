const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const grid = require('../js/pair-home-grid-layout.js');
test('canvas uses the finer 30 by 30 grid',()=>{
  assert.equal(grid.columns,30);
  assert.equal(grid.rows,30);
});
test('the 30 by 30 canvas keeps each grid cell physically square',()=>{
  const css=fs.readFileSync(path.join(__dirname,'../css/pair-home-grid.css'),'utf8');
  assert.match(css,/\.pair-grid-frame\s*\{[^}]*aspect-ratio:\s*1\s*\/\s*1/s);
  assert.doesNotMatch(css,/\.pair-grid-frame\s*\{[^}]*aspect-ratio:\s*(?:5\s*\/\s*4|3\s*\/\s*4)/s);
});
test('movement snaps by whole cells and stays inside all four edges',()=>{
  const start={x:3,y:4,w:4,h:3};
  assert.deepEqual(grid.move(start,.49,-.49),start);
  assert.deepEqual(grid.move(start,.51,1.51),{x:4,y:6,w:4,h:3});
  assert.deepEqual(grid.move(start,100,-100),{x:26,y:0,w:4,h:3});
  assert.deepEqual(start,{x:3,y:4,w:4,h:3});
});
test('resizing anchors the top left, uses whole cells and caps at the board',()=>{
  assert.deepEqual(grid.resize({x:28,y:27,w:1,h:2},20,20),{x:28,y:27,w:2,h:3});
  assert.deepEqual(grid.resize({x:1,y:1,w:2,h:2},-20,-20),{x:1,y:1,w:1,h:1});
});
test('fixed-ratio image frames resize in exact grid-cell multiples',()=>{
  assert.deepEqual(grid.ratioUnits('4:3'),{w:4,h:3});
  assert.equal(grid.ratioUnits('free'),null);
  assert.deepEqual(grid.resizeRatio({x:2,y:2,w:8,h:6},12,7,'width','4:3'),{x:2,y:2,w:12,h:9});
  assert.deepEqual(grid.resizeRatio({x:2,y:2,w:8,h:6},9,9,'height','4:3'),{x:2,y:2,w:12,h:9});
  assert.deepEqual(grid.resizeRatio({x:20,y:20,w:4,h:3},40,30,'width','4:3'),{x:20,y:20,w:8,h:6});
});
test('regular cards cannot overlap; stickers and shared edges are allowed',()=>{
  const widgets=[{id:'a',type:'profile',x:0,y:0,w:3,h:4},{id:'s',type:'sticker',x:3,y:0,w:20,h:20}];
  assert.equal(grid.available({x:2,y:2,w:3,h:3},widgets,'b'),false);
  assert.equal(grid.available({x:3,y:0,w:3,h:4},widgets,'b'),true);
  assert.equal(grid.available({x:0,y:0,w:3,h:4},widgets,'a'),true);
});
test('web frames are complete widgets and cannot overlap regular cards',()=>{
  const frame={id:'frame-a',type:'webframe',x:2,y:2,w:20,h:15};
  const card={id:'card',type:'text',x:5,y:5,w:5,h:4};
  assert.equal(grid.available(card,[frame],card.id,card.type),false);
  assert.equal(grid.available(frame,[card],frame.id,frame.type),false);
  assert.equal(grid.available({x:3,y:3,w:10,h:8},[frame],'frame-b','webframe'),false);
});
test('add and duplicate find actual vacant cells or report a full board',()=>{
  assert.deepEqual(grid.firstSpace({id:'b',w:3,h:3},[{id:'a',type:'text',x:0,y:0,w:3,h:3}]),{x:3,y:0,w:3,h:3});
  assert.equal(grid.firstSpace({id:'b',w:1,h:1},[{id:'a',type:'text',x:0,y:0,w:30,h:30}]),null);
});
test('snapping and resizing remain integral and bounded across a wide input range',()=>{
  for(let x=-15;x<=25;x+=.5)for(let size=-5;size<=20;size+=.5){
    const box=grid.snap({x,y:x,w:size,h:size});
    for(const value of Object.values(box))assert(Number.isInteger(value));
    assert(box.x>=0&&box.y>=0&&box.w>=1&&box.h>=1&&box.x+box.w<=grid.columns&&box.y+box.h<=grid.rows);
  }
});
test('all card themes include shared internal widget styling',()=>{
  const css=fs.readFileSync(path.join(__dirname,'../css/pair-home-grid.css'),'utf8');
  const script=fs.readFileSync(path.join(__dirname,'../js/pair-home-grid.js'),'utf8');
  for(const theme of ['flat','line','bold','soft','pixel','glass'])assert.match(css,new RegExp(`data-theme=${theme}`));
  for(const part of ['pair-profile-link','pair-bgm-play','pair-category-bar','pair-calendar-head','pair-widget-dday'])assert.match(css,new RegExp(part));
  assert.match(css,/pair-calendar-event/);
  assert.match(script,/eventMap/);
  assert.match(script,/일정 · 날짜\|내용/);
  assert.match(css,/pair-linkbanner-title/);
  assert.match(script,/배너 이미지 맞춤/);
  assert.match(css,/data-theme=pixel[^}]*input\[type=range\]/s);
  assert.match(script,/textColorSource==='custom'/);
  assert.match(script,/글자·아이콘 색/);
  assert.match(script,/도트 테마의 바깥 모서리는 직각으로 고정됩니다/);
  assert.match(script,/config\.boards/);
  assert.match(script,/게시판 추가/);
  assert.match(script,/이미 추가된 게시판입니다/);
});
test('BGM widget exposes every standard and pixel player layout',()=>{
  const css=fs.readFileSync(path.join(__dirname,'../css/pair-home-grid.css'),'utf8');
  const script=fs.readFileSync(path.join(__dirname,'../js/pair-home-grid.js'),'utf8');
  for(const style of ['mini','mini-pixel','cover','cover-pixel','album','album-pixel','lp','lp-pixel','sleeve','sleeve-pixel','backdrop','backdrop-pixel','deck']){
    assert.match(css,new RegExp(`bgm-style-${style}`));
    assert.match(script,new RegExp(`['"]${style}['"]`));
  }
  assert.match(css,/@keyframes pair-bgm-spin/);
  assert.match(css,/\.pair-bgm-vinyl i[^}]*width:\s*54%[^}]*background-position:\s*center/s);
  assert.match(css,/\.bgm-style-lp \.pair-bgm-visual[^}]*aspect-ratio:\s*1[^}]*var\(--card-bg/s);
  assert.match(css,/\.bgm-style-lp-pixel \.pair-bgm-vinyl[^}]*clip-path:\s*polygon/s);
  assert.match(css,/\.pair-widget-content\.pair-widget-bgm \.pair-bgm-play[^}]*background:\s*transparent\s*!important[^}]*box-shadow:\s*none\s*!important/s);
  assert.match(css,/bgm-style-deck input\[type=range\]::\-webkit-slider-runnable-track[^}]*height:\s*2px[^}]*border-radius:\s*0/s);
  assert.match(css,/bgm-style-deck input\[type=range\]::\-webkit-slider-thumb[^}]*width:\s*7px[^}]*border-radius:\s*0/s);
  assert.match(css,/bgm-style-sleeve-pixel \.pair-bgm-vinyl[^}]*clip-path:\s*polygon/s);
  assert.match(script,/pair-tonearm-tube/);
  assert.match(script,/--tonearm-angle/);
  assert.match(css,/\.bgm-style-deck \.pair-bgm-visual\s*\{[^}]*display:\s*grid/s);
  assert.match(css,/\.bgm-style-album \.pair-bgm-cover\s*\{[^}]*132px/s);
  assert.match(script,/function stopOtherAudio\(nextAudio\)/);
  assert.match(script,/audio\.addEventListener\('play',function\(\)\{stopOtherAudio\(audio\)/);
  assert.match(script,/다른 BGM은 자동으로 멈춥니다/);
  assert.match(script,/file\.size>50\*1024\*1024/);
  assert.match(script,/표지 초점 · 가로/);
  assert.match(script,/coverImage\.style\.objectPosition/);
  assert.match(script,/clearImage\.textContent='이미지 제거'/);
  assert.match(script,/프로필 사진 초점 · 가로/);
  assert.match(script,/headerFocusX/);
});
test('pixel cards use square outer corners and keep their fill clipped',()=>{
  const css=fs.readFileSync(path.join(__dirname,'../css/pair-home-grid.css'),'utf8');
  assert.match(css,/data-theme=pixel[^}]*overflow:\s*hidden[^}]*border-radius:\s*0\s*!important[^}]*clip-path:\s*none[^}]*background-clip:\s*padding-box/s);
});
